<?php

namespace Database\Seeders;

use App\Models\ActiveFlight;
use Illuminate\Database\Seeder;

class ActiveFlightSeeder extends Seeder
{
    private array $routes = [
        ['YSSY', -33.9461, 151.1772, 'YMML', -37.6733, 144.8433],
        ['YSSY', -33.9461, 151.1772, 'YBBN', -27.3842, 153.1175],
        ['YSSY', -33.9461, 151.1772, 'YPPH', -31.9403, 115.9664],
        ['YMML', -37.6733, 144.8433, 'YBBN', -27.3842, 153.1175],
        ['YMML', -37.6733, 144.8433, 'YBCS', -16.8858, 145.7553],
        ['YMML', -37.6733, 144.8433, 'NZAA', -37.0081, 174.7920],
        ['YBBN', -27.3842, 153.1175, 'YBCS', -16.8858, 145.7553],
        ['YBBN', -27.3842, 153.1175, 'WSSS', 1.3592, 103.9894],
        ['YPPH', -31.9403, 115.9664, 'WSSS', 1.3592, 103.9894],
        ['YPPH', -31.9403, 115.9664, 'YBCS', -16.8858, 145.7553],
        ['YSSY', -33.9461, 151.1772, 'NZAA', -37.0081, 174.7920],
        ['WSSS', 1.3592, 103.9894, 'RJTT', 35.5494, 139.7798],
    ];

    private array $aircraft = [
        ['reg' => 'ASR-B737', 'icao' => 'B737', 'type' => 'B737'],
        ['reg' => 'ASR-B738', 'icao' => 'B738', 'type' => 'B737'],
        ['reg' => 'ASR-A320', 'icao' => 'A320', 'type' => 'A320'],
        ['reg' => 'ASR-A21N', 'icao' => 'A21N', 'type' => 'A320'],
        ['reg' => 'ASR-B789', 'icao' => 'B789', 'type' => 'B787'],
        ['reg' => 'ASR-B77W', 'icao' => 'B77W', 'type' => 'B777'],
    ];

    private array $phases = ['preflight', 'boarding', 'departed', 'enroute', 'enroute', 'enroute', 'onapproach'];

    public function run(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $route = $this->routes[$i % count($this->routes)];
            $ac = $this->aircraft[$i % count($this->aircraft)];
            $phase = $this->phases[$i % count($this->phases)];
            $t = now()->subMinutes(rand(5, 120));

            $dep_lat = $route[1] + (rand(-100, 100) / 1000);
            $dep_lng = $route[2] + (rand(-100, 100) / 1000);
            $arr_lat = $route[4];
            $arr_lng = $route[5];

            $progress = match ($phase) {
                'preflight' => 0,
                'boarding' => 0.05,
                'departed' => 0.15,
                'enroute' => 0.3 + (rand(0, 40) / 100),
                'onapproach' => 0.85 + (rand(0, 10) / 100),
                default => 0.5,
            };

            $lat = $dep_lat + ($arr_lat - $dep_lat) * $progress + (rand(-50, 50) / 1000);
            $lng = $dep_lng + ($arr_lng - $dep_lng) * $progress + (rand(-50, 50) / 1000);

            $heading = round(rad2deg(atan2($arr_lng - $dep_lng, $arr_lat - $dep_lat)));

            $altitude = match ($phase) {
                'preflight' => 0,
                'boarding' => 0,
                'departed' => rand(1000, 5000),
                'enroute' => rand(28000, 40000),
                'onapproach' => rand(2000, 8000),
                default => 0,
            };

            $speed = match ($phase) {
                'preflight' => 0,
                'boarding' => 0,
                'departed' => rand(180, 280),
                'enroute' => rand(420, 510),
                'onapproach' => rand(140, 220),
                default => 0,
            };

            ActiveFlight::create([
                'flight_number' => 'ASR' . str_pad((string)(100 + $i), 3, '0', STR_PAD_LEFT),
                'aircraft_registration' => $ac['reg'],
                'aircraft_icao' => $ac['icao'],
                'aircraft_type' => $ac['type'],
                'departure' => $route[0],
                'arrival' => $route[3],
                'departure_lat' => $route[1],
                'departure_lng' => $route[2],
                'arrival_lat' => $route[4],
                'arrival_lng' => $route[5],
                'current_lat' => $lat,
                'current_lng' => $lng,
                'heading' => $heading,
                'altitude' => $altitude,
                'ground_speed' => $speed,
                'phase' => $phase,
                'status' => 'active',
                'started_at' => $t,
                'position_updated_at' => $t,
                'created_at' => $t,
                'updated_at' => now(),
            ]);
        }
    }
}
