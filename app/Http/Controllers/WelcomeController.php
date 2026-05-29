<?php

namespace App\Http\Controllers;

use App\Models\{Schedule, Aircraft, User, Rank, News, ActiveFlight, FlightPosition, Pirep};
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::select('flight_number', 'departure', 'arrival', 'aircraft_type', 'flight_time')
            ->limit(6)
            ->get();

        $aircraftCount  = Aircraft::count();
        $scheduleCount  = Schedule::count();
        $pilotCount     = User::count();
        $rankCount      = Rank::count();
        $news           = News::published()->with('author')->latest()->take(3)->get();

        // Active flights with full telemetry for Leaflet map
        $activeFlights = ActiveFlight::active()
            ->with('user')
            ->orderBy('position_updated_at', 'desc')
            ->get();

        $mappedFlights = $activeFlights->map(function ($f) {
            $breadcrumbs = FlightPosition::where('active_flight_id', $f->id)
                ->orderBy('recorded_at')
                ->get(['latitude', 'longitude'])
                ->map(fn($p) => [(float) $p->latitude, (float) $p->longitude])
                ->toArray();

            return [
                'id'                   => $f->id,
                'flight_number'        => $f->flight_number,
                'pilot_name'           => $f->user?->name ?? 'Unknown',
                'pilot_id'             => $f->user?->pilot_id ?? '',
                'aircraft_registration'=> $f->aircraft_registration,
                'aircraft_icao'        => $f->aircraft_icao,
                'aircraft_type'        => $f->aircraft_type,
                'departure'            => $f->departure,
                'arrival'              => $f->arrival,
                'departure_lat'        => (float) $f->departure_lat,
                'departure_lng'        => (float) $f->departure_lng,
                'arrival_lat'          => (float) $f->arrival_lat,
                'arrival_lng'          => (float) $f->arrival_lng,
                'current_lat'          => (float) $f->current_lat,
                'current_lng'          => (float) $f->current_lng,
                'heading'              => $f->heading ?? 0,
                'altitude'             => $f->altitude ?? 0,
                'ground_speed'         => $f->ground_speed ?? 0,
                'phase'                => $f->phase ?? 'enroute',
                'started_at'           => $f->started_at?->diffForHumans(),
                'position_updated_at'  => $f->position_updated_at?->diffForHumans(),
                'breadcrumbs'          => $breadcrumbs,
            ];
        });

        // Latest landings from approved PIREPs
        $latestLandings = Pirep::with('user')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Fallback: show any PIREP if none approved yet
        if ($latestLandings->isEmpty()) {
            $latestLandings = Pirep::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return view('welcome', compact(
            'schedules',
            'aircraftCount',
            'scheduleCount',
            'pilotCount',
            'rankCount',
            'news',
            'activeFlights',
            'mappedFlights',
            'latestLandings'
        ));
    }
}
