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

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Flight History</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Visual map of all your completed flights.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <span class="stat-label">Total Flights</span>
            <span class="stat-value">{{ $stats['total_flights'] ?? 0 }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Hours</span>
            <span class="stat-value">{{ number_format($stats['total_hours'] ?? 0, 1) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Destinations</span>
            <span class="stat-value">{{ $stats['unique_destinations'] ?? 0 }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Routes Flown</span>
            <span class="stat-value">{{ count($routes) }}</span>
        </div>
    </div>

    <div class="card p-5">
        <div id="flightHistoryMap" class="w-full h-[500px] rounded-xl z-0" style="min-height: 500px;"></div>
    </div>

    @if(!empty($stats['top_airports']))
    <div class="card p-5">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Most Visited Airports</h3>
        <div class="space-y-2">
            @foreach($stats['top_airports'] as $icao => $count)
            <div class="flex items-center justify-between text-sm">
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ $icao }}</span>
                <span class="text-slate-500">{{ $count }} {{ Str::plural('visit', $count) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(count($routes) === 0)
    <div class="card p-8 text-center text-slate-400 dark:text-slate-500">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
        </svg>
        <p>No completed flights yet</p>
        <p class="text-xs mt-1">Complete flights to see your history here.</p>
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
