<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimbriefService
{
    protected string $baseUrl = 'https://www.simbrief.com/api/xml.fetcher.php';

    public function fetchOFP(string $username): ?array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl, [
                'username' => $username,
                'json' => 1,
            ]);

            if (!$response->successful()) return null;

            $data = $response->json();

            if (empty($data) || isset($data['fetch']['error'])) return null;

            return $this->parseOFP($data);
        } catch (\Exception $e) {
            Log::warning("SimBrief fetch failed for {$username}: {$e->getMessage()}");
            return null;
        }
    }

    protected function parseOFP(array $raw): array
    {
        $general = $raw['general'] ?? [];
        $origin = $raw['origin'] ?? [];
        $dest = $raw['destination'] ?? [];
        $weather = $raw['weather'] ?? [];
        $fuel = $raw['fuel'] ?? [];
        $route = $raw['route'] ?? [];
        $params = $raw['params'] ?? [];
        $files = $raw['files'] ?? [];

        $waypoints = [];
        if (isset($route['navlog']) && is_array($route['navlog'])) {
            foreach ($route['navlog'] as $fix) {
                $waypoints[] = [
                    'ident' => $fix['ident'] ?? '',
                    'name' => $fix['name'] ?? '',
                    'lat' => $fix['pos_lat'] ?? null,
                    'lon' => $fix['pos_long'] ?? null,
                    'altitude' => $fix['altitude'] ?? null,
                    'distance' => $fix['distance'] ?? null,
                ];
            }
        }

        return [
            'flight_number' => $general['flight_number'] ?? '',
            'aircraft_icao' => $general['icao_actype'] ?? '',
            'departure' => $origin['icao_code'] ?? '',
            'departure_time' => $origin['std'] ?? '',
            'arrival' => $dest['icao_code'] ?? '',
            'arrival_time' => $dest['sta'] ?? '',
            'cruise_altitude' => $general['cruise_altitude'] ?? '',
            'route_raw' => $general['route'] ?? '',
            'distance' => $general['distance'] ?? '',
            'flight_time' => $general['flight_time'] ?? '',
            'fuel_ramp' => is_array($fuel['ramp'] ?? '') ? json_encode($fuel['ramp']) : ($fuel['ramp'] ?? ''),
            'fuel_trip' => is_array($fuel['trip'] ?? '') ? json_encode($fuel['trip']) : ($fuel['trip'] ?? ''),
            'fuel_block' => is_array($fuel['block'] ?? '') ? json_encode($fuel['block']) : ($fuel['block'] ?? ''),
            'fuel_plan_landing' => is_array($fuel['plan_landing'] ?? '') ? json_encode($fuel['plan_landing']) : ($fuel['plan_landing'] ?? ''),
            'weather_dep' => is_array($weather['dep'] ?? '') ? json_encode($weather['dep']) : ($weather['dep'] ?? ''),
            'weather_arr' => is_array($weather['dest'] ?? '') ? json_encode($weather['dest']) : ($weather['dest'] ?? ''),
            'weather_altn' => is_array($weather['altn'] ?? '') ? json_encode($weather['altn']) : ($weather['altn'] ?? ''),
            'waypoints' => $waypoints,
            'image_url' => $files['image'] ?? '',
            'pdf_url' => $files['pdf'] ?? '',
        ];
    }
}
