<?php

use App\Models\Pirep;
use Livewire\Volt\Component;

new class extends Component {
    public $routes = [];
    public $stats = [];

    public function mount(): void
    {
        $pireps = Pirep::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $airports = $this->getAirports();

        $routes = [];
        $airportStats = [];

        foreach ($pireps as $p) {
            $dep = $p->departure;
            $arr = $p->arrival;

            if (!isset($airports[$dep]) || !isset($airports[$arr])) continue;

            $routes[] = [
                'flight_number' => $p->flight_number,
                'departure' => $dep,
                'arrival' => $arr,
                'dep_lat' => $airports[$dep][0],
                'dep_lng' => $airports[$dep][1],
                'arr_lat' => $airports[$arr][0],
                'arr_lng' => $airports[$arr][1],
                'date' => $p->submitted_at?->format('d M Y') ?? $p->created_at->format('d M Y'),
            ];

            $airportStats[$dep] = ($airportStats[$dep] ?? 0) + 1;
            $airportStats[$arr] = ($airportStats[$arr] ?? 0) + 1;
        }

        $this->routes = $routes;

        arsort($airportStats);
        $this->stats = [
            'total_flights' => $pireps->count(),
            'total_hours' => $pireps->sum('flight_time'),
            'unique_destinations' => collect($pireps)->pluck('arrival')->unique()->count(),
            'top_airports' => array_slice($airportStats, 0, 5, true),
        ];
    }

    private function getAirports(): array
    {
        return [
            'YSSY' => [-33.9461, 151.1772],
            'YMML' => [-37.6733, 144.8433],
            'YBBN' => [-27.3842, 153.1175],
            'YPPH' => [-31.9385, 115.9672],
            'YPAD' => [-34.9450, 138.5311],
            'YBCS' => [-16.8852, 145.7554],
            'YPDN' => [-12.4083, 130.8725],
            'NZAA' => [-37.0081, 174.7917],
            'NZCH' => [-43.4894, 172.5322],
            'NZWN' => [-41.3272, 174.8050],
            'NFFN' => [-17.7550, 177.4433],
            'PGUM' => [13.4838, 144.7972],
            'WSSS' => [1.3592, 103.9894],
            'WMKK' => [2.7456, 101.7099],
            'VTBS' => [13.6811, 100.7470],
            'VHHH' => [22.3080, 113.9185],
            'ZSPD' => [31.1443, 121.7983],
            'RJAA' => [35.7647, 140.3864],
            'RJTT' => [35.5494, 139.7798],
            'RKSI' => [37.5587, 126.7896],
            'RPLL' => [14.5086, 121.0194],
            'VIDP' => [28.5562, 77.1000],
            'VABB' => [19.0887, 72.8679],
            'OMDB' => [25.2532, 55.3657],
            'OTHH' => [25.2606, 51.6138],
            'EHAM' => [52.3105, 4.7683],
            'EGLL' => [51.4700, -0.4543],
            'LFPG' => [49.0128, 2.5500],
            'EDDF' => [50.0333, 8.5706],
            'KJFK' => [40.6413, -73.7781],
            'KLAX' => [33.9425, -118.4081],
            'KORD' => [41.9742, -87.9073],
            'KATL' => [33.6407, -84.4277],
            'PHNL' => [21.3238, -157.9244],
            'KSFO' => [37.6213, -122.3790],
            'PAYA' => [59.5033, -139.6606],
            'CYYZ' => [43.6777, -79.6248],
            'SBGR' => [-23.4356, -46.4731],
            'SAEZ' => [-34.8222, -58.5358],
            'SCEL' => [-33.3930, -70.7858],
            'LPLA' => [38.7617, -27.0922],
            'LPPD' => [37.7430, -25.6983],
            'LPPT' => [38.7756, -9.1356],
            'LPMA' => [32.6941, -16.7785],
            'GVAC' => [16.7420, -22.9559],
            'GCLA' => [28.6261, -17.7553],
            'GCXO' => [28.4827, -16.3435],
            'GCTS' => [28.0444, -16.5725],
            'GMTT' => [35.7269, -5.9169],
            'GMMN' => [33.3670, -7.5900],
        ];
    }
}; ?>

<div class="sp-wrap">
    <div class="sp-title-area">
        <div class="sp-title">
            <i class="ph-fill ph-map-trifold"></i> Flight History
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-airplane-tilt"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;">{{ $stats['total_flights'] ?? 0 }}</div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Flights</div>
            </div>
        </div>

        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-clock"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;">{{ number_format($stats['total_hours'] ?? 0, 1) }}</div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Hours</div>
            </div>
        </div>

        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-map-pin"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;">{{ $stats['unique_destinations'] ?? 0 }}</div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Destinations</div>
            </div>
        </div>

        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-path"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;">{{ count($routes) }}</div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Routes Flown</div>
            </div>
        </div>
    </div>

    <div class="sp-card" style="margin-bottom: 24px; padding: 20px;">
        <div id="flightHistoryMap" style="width: 100%; height: 500px; border-radius: 8px; z-index: 0;"></div>
    </div>

    @if(!empty($stats['top_airports']))
    <div class="sp-card" style="margin-bottom: 24px;">
        <div class="sp-card-header">
            <div class="sp-card-title"><i class="ph-fill ph-star"></i> Most Visited Airports</div>
        </div>
        <div class="sp-card-body">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($stats['top_airports'] as $icao => $count)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--text-primary);">{{ $icao }}</span>
                    <span style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">{{ $count }} {{ Str::plural('visit', $count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(count($routes) === 0)
    <div class="sp-card">
        <div class="sp-empty">
            <i class="ph-fill ph-airplane-tilt"></i>
            <div style="font-size: 15px; font-weight: 700; color: var(--text-primary);">No completed flights yet</div>
            <div style="font-size: 13px; margin-top: 4px;">Complete flights to see your history here.</div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    const routes = @json($routes);
    if (routes.length === 0) return;

    const map = L.map('flightHistoryMap').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = [];

    routes.forEach(function (r) {
        const dep = [r.dep_lat, r.dep_lng];
        const arr = [r.arr_lat, r.arr_lng];

        bounds.push(dep);
        bounds.push(arr);

        // Departure marker
        L.circleMarker(dep, {
            radius: 5,
            fillColor: '#10b981',
            color: '#fff',
            weight: 2,
            fillOpacity: 1,
        }).addTo(map).bindPopup(`<b>${r.departure}</b><br>${r.flight_number}`);

        // Arrival marker
        L.circleMarker(arr, {
            radius: 5,
            fillColor: '#e11d48',
            color: '#fff',
            weight: 2,
            fillOpacity: 1,
        }).addTo(map).bindPopup(`<b>${r.arrival}</b><br>${r.flight_number}`);

        // Arc line (great circle approximation)
        const midLat = (dep[0] + arr[0]) / 2;
        const midLng = (dep[1] + arr[1]) / 2;
        const midOffset = 0.2 * Math.sqrt(Math.pow(dep[0] - arr[0], 2) + Math.pow(dep[1] - arr[1], 2));
        const controlLat = midLat + (dep[0] < arr[0] ? 1 : -1) * midOffset;

        const curvePoints = [];
        for (let t = 0; t <= 1; t += 0.02) {
            const lat = (1 - t) * (1 - t) * dep[0] + 2 * (1 - t) * t * controlLat + t * t * arr[0];
            const lng = (1 - t) * (1 - t) * dep[1] + 2 * (1 - t) * t * midLng + t * t * arr[1];
            curvePoints.push([lat, lng]);
        }

        L.polyline(curvePoints, {
            color: '#e11d48',
            weight: 1.5,
            opacity: 0.5,
        }).addTo(map).bindPopup(`<b>${r.flight_number}</b><br>${r.departure} → ${r.arrival}<br>${r.date}`);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
});
</script>
@endpush
