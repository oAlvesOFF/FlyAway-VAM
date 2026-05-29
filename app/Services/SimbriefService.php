<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimbriefService
{
    protected string $baseUrl = 'https://www.simbrief.com/api/xml.fetcher.php';

    /**
     * Fetch the latest OFP using either a numeric SimBrief Pilot ID or a username.
     * Prefers userid when available.
     */
    public function fetchOFP(?string $username, ?string $userid = null): ?array
    {
        $params = ['json' => 1];

        if ($userid) {
            $params['userid'] = $userid;
        } elseif ($username) {
            $params['username'] = $username;
        } else {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($this->baseUrl, $params);

            if (!$response->successful()) return null;

            $data = $response->json();

            if (empty($data) || isset($data['fetch']['error'])) return null;

            return $this->parseOFP($data);
        } catch (\Exception $e) {
            $identifier = $userid ?? $username;
            Log::warning("SimBrief fetch failed for {$identifier}: {$e->getMessage()}");
            return null;
        }
    }

    protected function parseOFP(array $raw): array
    {
        $general  = $raw['general']     ?? [];
        $origin   = $raw['origin']      ?? [];
        $dest     = $raw['destination'] ?? [];
        $altn     = $raw['alternate']   ?? ($raw['alternates'][0] ?? []);
        $weather  = $raw['weather']     ?? [];
        $fuel     = $raw['fuel']        ?? [];
        $route    = $raw['route']       ?? [];
        $params   = $raw['params']      ?? [];
        $files    = $raw['files']       ?? [];
        $pax      = $raw['pax']         ?? [];
        $weights  = $raw['weights']     ?? [];
        $prefile  = $raw['prefile']     ?? [];
        $fms      = $raw['fms_downloads'] ?? [];

        $waypoints = [];
        if (isset($route['navlog']) && is_array($route['navlog'])) {
            foreach ($route['navlog'] as $fix) {
                $waypoints[] = [
                    'ident'    => $fix['ident']     ?? '',
                    'name'     => $fix['name']      ?? '',
                    'lat'      => $fix['pos_lat']   ?? null,
                    'lon'      => $fix['pos_long']  ?? null,
                    'altitude' => $fix['altitude']  ?? null,
                    'distance' => $fix['distance']  ?? null,
                    'airway'   => $fix['via_airway'] ?? '',
                ];
            }
        }

        // Fuel helper – extract numeric value from possible array
        $fuelVal = function ($v) {
            if (is_array($v)) {
                return $v['ppounds'] ?? $v['kgs'] ?? $v['lbs'] ?? json_encode($v);
            }
            return $v ?? '';
        };

        // Alternate airport
        $altnIcao = '';
        $altnName = '';
        if (is_array($altn)) {
            $altnIcao = $altn['icao_code'] ?? $altn['icao'] ?? '';
            $altnName = $altn['name'] ?? '';
        }
        // Also try the weather altn field
        $weatherAltn = $weather['altn'] ?? [];
        if (is_array($weatherAltn)) {
            $weatherAltnStr = implode("\n", array_map(fn($w) => is_array($w) ? json_encode($w) : $w, $weatherAltn));
        } else {
            $weatherAltnStr = $weatherAltn;
        }

        // Passengers & cargo
        $paxCount  = is_array($pax) ? ($pax['count'] ?? $pax['pax_count'] ?? '') : $pax;
        $paxWeight = is_array($pax) ? ($pax['weight'] ?? '') : '';
        $cargo     = is_array($weights) ? ($weights['payload'] ?? '') : '';

        $filesDir = is_string($files['directory'] ?? null) ? $files['directory'] : '';
        $filesImg = is_string($files['image'] ?? null) ? $files['image'] : '';
        $filesPdf = is_string($files['pdf'] ?? null) ? $files['pdf'] : '';

        return [
            'flight_number'    => $general['flight_number']   ?? '',
            'aircraft_icao'    => $general['icao_actype']     ?? '',
            'aircraft_reg'     => $params['reg']              ?? '',
            'departure'        => $origin['icao_code']        ?? '',
            'departure_name'   => $origin['name']             ?? '',
            'departure_time'   => $origin['std']              ?? '',
            'arrival'          => $dest['icao_code']          ?? '',
            'arrival_name'     => $dest['name']               ?? '',
            'arrival_time'     => $dest['sta']                ?? '',
            'alternate'        => $altnIcao,
            'alternate_name'   => $altnName,
            'cruise_altitude'  => $general['cruise_altitude'] ?? '',
            'route_raw'        => $general['route']           ?? '',
            'distance'         => $general['distance']        ?? '',
            'flight_time'      => $general['flight_time']     ?? '',
            'pax'              => $paxCount,
            'pax_weight'       => $paxWeight,
            'cargo'            => $cargo,
            'fuel_ramp'        => $fuelVal($fuel['ramp']         ?? ''),
            'fuel_trip'        => $fuelVal($fuel['trip']         ?? ''),
            'fuel_block'       => $fuelVal($fuel['block']        ?? ''),
            'fuel_plan_landing'=> $fuelVal($fuel['plan_landing'] ?? ''),
            'fuel_contingency' => $fuelVal($fuel['contingency']  ?? ''),
            'fuel_alternate'   => $fuelVal($fuel['alternate']    ?? ''),
            'fuel_reserve'     => $fuelVal($fuel['reserve']      ?? ''),
            'fuel_extra'       => $fuelVal($fuel['extra']        ?? ''),
            'fuel_unit'        => $fuel['units'] ?? 'lbs',
            'weather_dep'      => is_array($weather['dep']  ?? '') ? json_encode($weather['dep'])  : ($weather['dep']  ?? ''),
            'weather_arr'      => is_array($weather['dest'] ?? '') ? json_encode($weather['dest']) : ($weather['dest'] ?? ''),
            'weather_altn'     => $weatherAltnStr,
            'waypoints'        => $waypoints,
            'image_url'        => $filesDir ?: $filesImg,
            'pdf_url'          => $filesPdf,
            'pdf_link'         => ($filesDir && $filesPdf)
                                    ? rtrim($filesDir, '/') . '/' . ltrim($filesPdf, '/')
                                    : $filesPdf,
            'prefile'          => $prefile,
            'fms_downloads'    => $fms,
        ];
    }
}
