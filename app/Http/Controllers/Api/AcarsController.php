<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Acars;
use App\Models\Pirep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * AcarsController
 *
 * Handles live telemetry ingestion from the ACARS client (Tauri/Rust app).
 * The ACARS client sends position pings every 1 second via update_flight_position().
 * This controller stores them as ACARS records linked to a Pirep in-progress.
 *
 * Endpoints used by the ACARS client (api.rs):
 *   POST /api/acars/position  → update_flight_position() via POST /api/flights/track (we redirect here)
 *   POST /api/acars/log       → manual event log injection
 */
class AcarsController extends Controller
{
    /**
     * Receive a live position ping from the ACARS client and store it.
     *
     * The ACARS client (api.rs) sends a FlightTelemetry struct with fields:
     *   flight_number, aircraft_registration, aircraft_icao, aircraft_type,
     *   departure, arrival, current_lat, current_lng, heading, altitude, ground_speed, phase
     */
    public function position(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'flight_number'         => 'required|string|max:20',
            'aircraft_registration' => 'required|string|max:20',
            'aircraft_icao'         => 'required|string|max:10',
            'current_lat'           => 'required|numeric|between:-90,90',
            'current_lng'           => 'required|numeric|between:-180,180',
            'heading'               => 'nullable|integer|between:0,360',
            'altitude'              => 'nullable|integer',
            'ground_speed'          => 'nullable|integer|min:0',
            'phase'                 => 'nullable|string|max:30',
            'vs'                    => 'nullable|numeric',
            'ias'                   => 'nullable|integer|min:0',
            'fuel_flow'             => 'nullable|numeric|min:0',
            'fuel_remaining_kg'     => 'nullable|numeric|min:0',
            'sim_time'              => 'nullable|string',
        ]);

        $userId = Auth::id();

        // Find the active PIREP for this pilot and flight (state = in progress / draft)
        $pirep = Pirep::where('user_id', $userId)
            ->where('flight_number', $validated['flight_number'])
            ->whereIn('status', ['pending', 'draft', 'in_progress'])
            ->latest()
            ->first();

        if (!$pirep) {
            // Auto-create a draft PIREP when the ACARS first pings us
            $pirep = Pirep::create([
                'user_id'               => $userId,
                'flight_number'         => $validated['flight_number'],
                'departure'             => $request->input('departure', 'ZZZZ'),
                'arrival'               => $request->input('arrival', 'ZZZZ'),
                'aircraft_registration' => $validated['aircraft_registration'],
                'aircraft_icao'         => $validated['aircraft_icao'],
                'flight_time'           => 0,
                'status'                => 'draft',
                'source'                => 1, // 1 = ACARS
                'state'                 => 0, // 0 = In Progress
                'submitted_at'          => null,
                'block_off_time'        => now(),
            ]);
        }

        // Determine ACARS type from phase
        // 0 = FLIGHT_PATH, 1 = LOG, 2 = ROUTE
        $acarsType = 0;

        $simTime = null;
        if (!empty($validated['sim_time'])) {
            try {
                $simTime = \Carbon\Carbon::parse($validated['sim_time']);
            } catch (\Exception $e) {
                $simTime = null;
            }
        }

        $acars = Acars::create([
            'id'           => Str::uuid()->toString(),
            'pirep_id'     => $pirep->id,
            'type'         => $acarsType,
            'lat'          => $validated['current_lat'],
            'lon'          => $validated['current_lng'],
            'heading'      => $validated['heading'] ?? 0,
            'altitude_msl' => $validated['altitude'] ?? 0,
            'altitude_agl' => $validated['altitude'] ?? 0,
            'gs'           => $validated['ground_speed'] ?? 0,
            'ias'          => $validated['ias'] ?? null,
            'vs'           => $validated['vs'] ?? null,
            'fuel_flow'    => $validated['fuel_flow'] ?? null,
            'status'       => $validated['phase'] ?? 'enroute',
            'source'       => 1, // ACARS
            'sim_time'     => $simTime,
        ]);

        // Update fuel remaining on the PIREP if provided
        if (isset($validated['fuel_remaining_kg'])) {
            $pirep->update(['block_fuel' => $validated['fuel_remaining_kg']]);
        }

        return response()->json([
            'acars_id' => $acars->id,
            'pirep_id' => $pirep->id,
            'phase'    => $validated['phase'] ?? 'enroute',
        ], 201);
    }

    /**
     * Receive a manual log event from the ACARS client.
     * e.g. "Gear Up", "Flaps 1", "Autopilot Disconnected"
     */
    public function log(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'flight_number' => 'required|string|max:20',
            'log'           => 'required|string|max:500',
            'phase'         => 'nullable|string|max:30',
            'sim_time'      => 'nullable|string',
        ]);

        $userId = Auth::id();

        $pirep = Pirep::where('user_id', $userId)
            ->where('flight_number', $validated['flight_number'])
            ->whereIn('status', ['pending', 'draft', 'in_progress'])
            ->latest()
            ->first();

        if (!$pirep) {
            return response()->json(['error' => 'No active PIREP found for this flight'], 404);
        }

        $simTime = null;
        if (!empty($validated['sim_time'])) {
            try {
                $simTime = \Carbon\Carbon::parse($validated['sim_time']);
            } catch (\Exception $e) {
                $simTime = null;
            }
        }

        $logEntry = Acars::create([
            'id'       => Str::uuid()->toString(),
            'pirep_id' => $pirep->id,
            'type'     => 1, // 1 = LOG
            'log'      => $validated['log'],
            'status'   => $validated['phase'] ?? null,
            'source'   => 1,
            'sim_time' => $simTime,
        ]);

        return response()->json(['log_id' => $logEntry->id], 201);
    }

    /**
     * Return the full ACARS position history for a given PIREP.
     * Useful for drawing flight paths on the live map.
     */
    public function history(Pirep $pirep): JsonResponse
    {
        $positions = Acars::where('pirep_id', $pirep->id)
            ->where('type', 0) // FLIGHT_PATH only
            ->orderBy('created_at', 'asc')
            ->get(['id', 'lat', 'lon', 'heading', 'altitude_msl', 'gs', 'vs', 'status', 'sim_time', 'created_at']);

        return response()->json($positions);
    }
}
