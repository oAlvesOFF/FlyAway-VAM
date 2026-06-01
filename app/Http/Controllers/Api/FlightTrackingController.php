<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveFlight;
use App\Models\FlightPosition;
use App\Services\DiscordWebhookService;
use App\Services\MqttService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class FlightTrackingController extends Controller
{
    #[OA\Get(path: '/api/flights/active', summary: 'Get active flights', description: 'Returns all currently active (in-progress) flights with real-time telemetry data. Public endpoint, no auth required.', tags: ['Flights'], responses: [
        new OA\Response(response: 200, description: 'List of active flights', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ActiveFlight'))),
    ])]
    public function index(): JsonResponse
    {
        $flights = ActiveFlight::active()->orderBy('position_updated_at', 'desc')->get();
        return response()->json($flights);
    }

    #[OA\Post(path: '/api/flights/track', summary: 'Update flight position', description: 'Updates the real-time position and telemetry of an active flight. Requires API authentication.', tags: ['Flights'], security: [['apiAuth' => []]], responses: [
        new OA\Response(response: 201, description: 'Flight position updated'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ])]
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'flight_number'        => 'required|string|max:20',
            'aircraft_registration' => 'required|string|max:20',
            'aircraft_icao'        => 'required|string|max:10',
            'aircraft_type'        => 'required|string|max:100',
            'departure'            => 'required|string|max:4',
            'arrival'              => 'required|string|max:4',
            'departure_lat'        => 'nullable|numeric|min:-90|max:90',
            'departure_lng'        => 'nullable|numeric|min:-180|max:180',
            'arrival_lat'          => 'nullable|numeric|min:-90|max:90',
            'arrival_lng'          => 'nullable|numeric|min:-180|max:180',
            'current_lat'          => 'required|numeric|min:-90|max:90',
            'current_lng'          => 'required|numeric|min:-180|max:180',
            'heading'              => 'nullable|integer|min:0|max:360',
            'altitude'             => 'nullable|integer|min:0|max:60000',
            'ground_speed'         => 'nullable|integer|min:0|max:1000',
            'phase'                => 'nullable|string|in:preflight,boarding,departed,enroute,onapproach,landed',
        ]);

        $flight = ActiveFlight::where('flight_number', $validated['flight_number'])
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if ($flight) {
            // Guardar phase antiga ANTES do update para comparar depois
            $oldPhase = $flight->phase;
            $newPhase = $validated['phase'] ?? $flight->phase;

            $flight->update([
                'current_lat' => $validated['current_lat'],
                'current_lng' => $validated['current_lng'],
                'heading' => $validated['heading'] ?? $flight->heading,
                'altitude' => $validated['altitude'] ?? $flight->altitude,
                'ground_speed' => $validated['ground_speed'] ?? $flight->ground_speed,
                'phase' => $newPhase,
                'position_updated_at' => now(),
            ]);

            // Disparar webhook Discord apenas se a phase mudou
            if ($oldPhase !== $newPhase) {
                app(DiscordWebhookService::class)->sendFlightStatus($flight);
            }
        } else {
            $flight = ActiveFlight::create([
                'flight_number' => $validated['flight_number'],
                'aircraft_registration' => $validated['aircraft_registration'],
                'aircraft_icao' => $validated['aircraft_icao'],
                'aircraft_type' => $validated['aircraft_type'],
                'departure' => $validated['departure'],
                'arrival' => $validated['arrival'],
                'departure_lat' => $validated['departure_lat'] ?? null,
                'departure_lng' => $validated['departure_lng'] ?? null,
                'arrival_lat' => $validated['arrival_lat'] ?? null,
                'arrival_lng' => $validated['arrival_lng'] ?? null,
                'current_lat' => $validated['current_lat'],
                'current_lng' => $validated['current_lng'],
                'heading' => $validated['heading'] ?? 0,
                'altitude' => $validated['altitude'] ?? 0,
                'ground_speed' => $validated['ground_speed'] ?? 0,
                'phase' => $validated['phase'] ?? 'enroute',
                'status' => 'active',
                'user_id' => Auth::id(),
                'started_at' => now(),
                'position_updated_at' => now(),
            ]);

            // Notificar Discord sobre novo voo iniciado
            app(DiscordWebhookService::class)->sendFlightStatus($flight);
        }

        FlightPosition::create([
            'active_flight_id' => $flight->id,
            'flight_number' => $flight->flight_number,
            'latitude' => $validated['current_lat'],
            'longitude' => $validated['current_lng'],
            'heading' => $validated['heading'] ?? $flight->heading,
            'altitude' => $validated['altitude'] ?? $flight->altitude,
            'ground_speed' => $validated['ground_speed'] ?? $flight->ground_speed,
            'phase' => $validated['phase'] ?? $flight->phase,
            'recorded_at' => now(),
        ]);

        app(MqttService::class)->publish("flyaway/flights/{$flight->id}/track", $flight->toArray());

        return response()->json($flight);
    }

    #[OA\Post(path: '/api/flights/{flight}/complete', summary: 'Complete a flight', description: 'Marks an active flight as completed. Requires API authentication.', tags: ['Flights'], security: [['apiAuth' => []]], parameters: [
        new OA\Parameter(name: 'flight', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ], responses: [
        new OA\Response(response: 200, description: 'Flight completed'),
        new OA\Response(response: 404, description: 'Flight not found'),
    ])]
    public function complete(Request $request, ActiveFlight $flight): JsonResponse
    {
        $flight->update([
            'status' => 'completed',
            'phase' => 'landed',
            'position_updated_at' => now(),
            'ended_at' => now(),
        ]);

        // Notificar Discord que o voo pousou
        app(DiscordWebhookService::class)->sendFlightStatus($flight);

        app(MqttService::class)->publish("flyaway/flights/{$flight->id}/complete", [
            'flight_number' => $flight->flight_number,
            'status' => 'completed',
        ]);

        return response()->json(['message' => 'Flight marked as completed']);
    }
}
