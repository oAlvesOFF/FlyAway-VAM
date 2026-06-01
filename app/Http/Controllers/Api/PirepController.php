<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Aircraft;
use App\Models\Pirep;
use App\Models\Rank;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PirepApproved;
use App\Notifications\PirepRejected;
use App\Services\DiscordWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class PirepController extends Controller
{
    #[OA\Get(path: '/api/pireps', summary: 'List PIREPs', description: 'Returns a paginated list of PIREPs, optionally filtered by status. Requires API authentication.', tags: ['PIREPs'], security: [['apiAuth' => []]], parameters: [
        new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected'])),
    ], responses: [
        new OA\Response(response: 200, description: 'Paginated PIREP list', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Pirep'))),
    ])]
    public function index(Request $request): JsonResponse
    {
        $query = Pirep::with('user')->where('user_id', Auth::id());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(50));
    }

    #[OA\Post(path: '/api/pireps', summary: 'File a PIREP', description: 'Creates a new PIREP. Score is auto-calculated from landing rate. Requires API authentication.', tags: ['PIREPs'], security: [['apiAuth' => []]], responses: [
        new OA\Response(response: 201, description: 'PIREP created', content: new OA\JsonContent(ref: '#/components/schemas/Pirep')),
        new OA\Response(response: 422, description: 'Validation error'),
    ])]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'               => 'nullable|integer|exists:users,id',
            'pilot_id'              => 'nullable|string|max:20',
            'flight_number'         => 'required|string|max:20',
            'departure'             => 'required|string|size:4',
            'arrival'               => 'required|string|size:4',
            'aircraft_registration' => 'required|string|max:20',
            'aircraft_icao'         => 'required|string|max:10',
            'flight_time'           => 'required|numeric|min:0.01|max:30',
            'landing_rate'          => 'nullable|integer|min:-2000|max:2000',
            'route'                 => 'nullable|string',
            'log'                   => 'nullable|string',
            // Advanced fields from ACARS client
            'block_time'            => 'nullable|integer|min:0',
            'block_fuel'            => 'nullable|numeric|min:0',
            'fuel_used'             => 'nullable|numeric|min:0',
            'zfw'                   => 'nullable|numeric|min:0',
        ]);

        // Resolve user
        $userId = Auth::id() ?? $validated['user_id'] ?? null;
        if (!$userId && !empty($validated['pilot_id'])) {
            $user = User::where('pilot_id', $validated['pilot_id'])->first();
            $userId = $user?->id;
        }
        if (!$userId) {
            return response()->json(['error' => 'User not found'], 422);
        }

        // Duplicate check: prevent double-clicks or spam by checking the last 5 minutes
        $existing = Pirep::where('user_id', $userId)
            ->where('flight_number', $validated['flight_number'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();
        if ($existing) {
            return response()->json(['error' => 'PIREP already filed for this flight recently. Please wait a few minutes before submitting again.'], 422);
        }

        // Score from absolute landing rate (null if not captured)
        $validated['landing_rate'] = isset($validated['landing_rate']) ? (int) $validated['landing_rate'] : null;
        $lr = abs((int) ($validated['landing_rate'] ?? 0));
        $score = match (true) {
            $lr > 500 => 60,
            $lr > 50  => 80,
            default   => 100,
        };

        $validated['user_id']      = $userId;
        $validated['score']        = $score;
        $validated['submitted_at'] = now();
        $validated['source']       = 1; // 1 = ACARS
        $validated['state']        = 0; // In progress → will be set to completed/accepted

        // If pilot had an in-progress draft PIREP from ACARS live tracking, promote it
        $draftPirep = Pirep::where('user_id', $userId)
            ->where('flight_number', $validated['flight_number'])
            ->where('status', 'draft')
            ->latest()
            ->first();

        if ($draftPirep) {
            $draftPirep->update([
                'status'        => $score >= (int) Setting::get('auto_approve_threshold', 90) ? 'approved' : 'pending',
                'flight_time'   => $validated['flight_time'],
                'landing_rate'  => $validated['landing_rate'],
                'score'         => $score,
                'log'           => $validated['log'] ?? $draftPirep->log,
                'block_time'    => $validated['block_time'] ?? null,
                'block_fuel'    => $validated['block_fuel'] ?? null,
                'fuel_used'     => $validated['fuel_used'] ?? null,
                'zfw'           => $validated['zfw'] ?? null,
                'submitted_at'  => now(),
                'block_on_time' => now(),
            ]);
            $pirep = $draftPirep->fresh();
        } else {
            // Auto-approve if score meets threshold
            $threshold = (int) Setting::get('auto_approve_threshold', 90);
            if ($score >= $threshold) {
                $validated['status'] = 'approved';
                $pirep = Pirep::create($validated);
                $this->processApproval($pirep, $pirep->user);
            } else {
                $validated['status'] = 'pending';
                $pirep = Pirep::create($validated);
            }
        }

        if ($pirep->status === 'approved') {
            $this->processApproval($pirep, $pirep->user);
        }

        // Close any active flight for this user and flight number
        $activeFlight = \App\Models\ActiveFlight::where('user_id', $userId)
            ->where('flight_number', $validated['flight_number'])
            ->where('status', 'active')
            ->first();

        if ($activeFlight) {
            $activeFlight->update([
                'status'              => 'completed',
                'phase'               => 'landed',
                'position_updated_at' => now(),
                'ended_at'            => now(),
            ]);

            app(\App\Services\MqttService::class)->publish("flyaway/flights/{$activeFlight->id}/complete", [
                'flight_number' => $activeFlight->flight_number,
                'status'        => 'completed',
            ]);
        }

        return response()->json($pirep, 201);
    }

    #[OA\Get(path: '/api/pireps/{pirep}', summary: 'Get PIREP details', description: 'Returns a single PIREP with full details. Requires API authentication.', tags: ['PIREPs'], security: [['apiAuth' => []]], parameters: [
        new OA\Parameter(name: 'pirep', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], responses: [
        new OA\Response(response: 200, description: 'PIREP details', content: new OA\JsonContent(ref: '#/components/schemas/Pirep')),
    ])]
    public function show(Pirep $pirep): JsonResponse
    {
        return response()->json($pirep->load('user'));
    }

    #[OA\Post(path: '/api/pireps/{pirep}/approve', summary: 'Approve a PIREP', description: 'Approves a pending PIREP. Updates pilot hours, flights count, rank, and aircraft location. Sends notification. Requires API authentication.', tags: ['PIREPs'], security: [['apiAuth' => []]], parameters: [
        new OA\Parameter(name: 'pirep', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], responses: [
        new OA\Response(response: 200, description: 'PIREP approved'),
        new OA\Response(response: 404, description: 'PIREP not found'),
    ])]
    protected function processApproval(Pirep $pirep, ?User $user): void
    {
        if ($user) {
            $user->increment('total_hours', $pirep->flight_time);
            $user->increment('total_flights');
            $user->update(['last_location' => $pirep->arrival]);

            $newRank = Rank::where('minimum_hours', '<=', $user->total_hours)
                ->orderBy('minimum_hours', 'desc')->first();
            if ($newRank) {
                $user->update(['rank_id' => $newRank->id]);
            }

            $user->notify(new PirepApproved($pirep));
            Achievement::checkAndUnlock($user);
        }

        Aircraft::where('registration', $pirep->aircraft_registration)
            ->update([
                'location' => $pirep->arrival,
                'total_hours_since_service' => DB::raw('total_hours_since_service + ' . $pirep->flight_time),
            ]);
    }

    public function approve(Pirep $pirep): JsonResponse
    {
        $pirep->update(['status' => 'approved']);
        $this->processApproval($pirep, $pirep->user);
        // Notificar Discord sobre aprovação
        app(DiscordWebhookService::class)->sendPirepStatus($pirep);
        return response()->json(['message' => 'PIREP approved']);
    }

    #[OA\Post(path: '/api/pireps/{pirep}/reject', summary: 'Reject a PIREP', description: 'Rejects a pending PIREP. Sends notification to pilot. Requires API authentication.', tags: ['PIREPs'], security: [['apiAuth' => []]], parameters: [
        new OA\Parameter(name: 'pirep', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], responses: [
        new OA\Response(response: 200, description: 'PIREP rejected'),
        new OA\Response(response: 404, description: 'PIREP not found'),
    ])]
    public function reject(Request $request, Pirep $pirep): JsonResponse
    {
        $reason = $request->input('reason');
        $pirep->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        if ($pirep->user) {
            $pirep->user->notify(new PirepRejected($pirep));
        }

        // Notificar Discord sobre rejeição
        app(DiscordWebhookService::class)->sendPirepStatus($pirep);

        return response()->json(['message' => 'PIREP rejected']);
    }
}
