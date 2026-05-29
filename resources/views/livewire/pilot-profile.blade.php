<?php
use App\Models\User;
use App\Models\Pirep;
use App\Models\Airport;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $user;
    public $stats = [];
    public $chartData = [];
    public $passportStats = [];
    public $activeTab = 'Flights';
    public $tourProgress = [];

    public function mount($pilotId): void
    {
        $this->user = User::with(['rank', 'pireps', 'achievements', 'tours'])->findOrFail($pilotId);
        $this->calculateStats();
        $this->prepareChartData();
        $this->computePassportStats();
        $this->computeTourProgress();
    }

    public function computeTourProgress(): void
    {
        $flights = $this->user->pireps()->select('departure', 'arrival')->get();

        foreach ($this->user->tours as $tour) {
            $waypoints = $tour->waypoints;
            if (empty($waypoints) || count($waypoints) < 2) {
                $this->tourProgress[$tour->id] = ['pct' => 0, 'completed' => false];
                continue;
            }

            $segments = count($waypoints) - 1;
            $completed = 0;

            for ($i = 0; $i < $segments; $i++) {
                if ($flights->contains(fn($f) => $f->departure === $waypoints[$i] && $f->arrival === $waypoints[$i + 1])) {
                    $completed++;
                }
            }

            $pct = $segments > 0 ? round(($completed / $segments) * 100) : 0;
            $this->tourProgress[$tour->id] = ['pct' => $pct, 'completed' => $pct >= 100];
        }
    }

    public function calculateStats(): void
    {
        $pirepsQuery = $this->user->pireps()->where('status', 'approved');
        $totalFlights = $pirepsQuery->count();

        $dates = $pirepsQuery->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d));

        $dayStreak = 0;
        $bestStreak = 0;
        $currentRun = 0;
        $prevDate = null;

        foreach ($dates as $date) {
            if ($prevDate === null) {
                $currentRun = 1;
            } else {
                $diff = $prevDate->diffInDays($date);
                $currentRun = $diff === 1 ? $currentRun + 1 : 1;
            }
            $bestStreak = max($bestStreak, $currentRun);
            $prevDate = $date;
        }

        if ($dates->isNotEmpty()) {
            $mostRecent = $dates->first();
            $dayStreak = $mostRecent->diffInDays(now()->startOfDay()) <= 1 ? $currentRun : 0;
        }

        $this->stats = [
            'totalHours' => $this->user->total_hours,
            'totalFlights' => $totalFlights,
            'avgLanding' => $pirepsQuery->avg('landing_rate') ?? 0,
            'dayStreak' => $dayStreak,
            'bestStreak' => $bestStreak,
            'balance' => 0,
        ];
    }

    public function prepareChartData(): void
    {
        $pireps = $this->user->pireps()->where('status', 'approved')->get();

        $airportCodes = $pireps->pluck('arrival')->merge($pireps->pluck('departure'))->unique();
        $airports = Airport::whereIn('icao', $airportCodes)->get()->keyBy('icao');

        $destinations = [];
        $routes = [];

        foreach ($pireps as $pirep) {
            $airport = $airports->get($pirep->arrival);
            $country = $airport?->country ?? 'Unknown';
            $destinations[$country] = ($destinations[$country] ?? 0) + 1;

            $dep = $airports->get($pirep->departure);
            $arr = $airports->get($pirep->arrival);
            if ($dep && $arr) {
                $routes[] = [
                    'from' => ['lat' => $dep->lat, 'lng' => $dep->lng],
                    'to' => ['lat' => $arr->lat, 'lng' => $arr->lng],
                ];
            }
        }

        $this->chartData = [
            'destinations' => $destinations ?: ['No Data' => 1],
            'routes' => $routes,
        ];
    }

    public function computePassportStats(): void
    {
        $pireps = $this->user->pireps()->where('status', 'approved')->get();

        $codes = $pireps->pluck('arrival')->merge($pireps->pluck('departure'))->unique();
        $airports = Airport::whereIn('icao', $codes)->get()->keyBy('icao');

        $visitedAirports = [];
        $countries = [];
        $totalDistance = 0;

        foreach ($pireps as $pirep) {
            $code = $pirep->arrival;
            $visitedAirports[$code] = ($visitedAirports[$code] ?? 0) + 1;

            $arrAirport = $airports->get($code);
            if ($arrAirport && $arrAirport->country) {
                $countries[$arrAirport->country] = true;
            }

            $dep = $airports->get($pirep->departure);
            $arr = $airports->get($pirep->arrival);
            if ($dep && $arr) {
                $totalDistance += $this->haversineDistance(
                    $dep->lat, $dep->lng, $arr->lat, $arr->lng
                );
            }
        }

        $mostVisited = $visitedAirports ? array_search(max($visitedAirports), $visitedAirports) : 'N/A';

        $this->passportStats = [
            'uniqueAirports' => count($visitedAirports),
            'countries' => count($countries),
            'mostVisited' => $mostVisited,
            'totalDistance' => round($totalDistance),
            'airports' => $visitedAirports,
        ];
    }

    private function haversineDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad((float) $lat2 - (float) $lat1);
        $dLng = deg2rad((float) $lng2 - (float) $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad((float) $lat1)) * cos(deg2rad((float) $lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}; ?>

<div class="min-h-screen bg-[#050505] text-white p-6 font-sans">
    <div class="max-w-7xl mx-auto space-y-8">
        
        {{-- Modern Profile Header --}}
        <div class="flex items-center gap-6 p-6 bg-[#0a0a0a] rounded-2xl border border-[#1a1a1a]">
            {{-- Avatar --}}
            <div class="w-16 h-16 rounded-lg overflow-hidden bg-[#10b981] flex items-center justify-center text-2xl font-bold text-white shrink-0 border border-emerald-400/30">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($user->name, 0, 1) }}
                @endif
            </div>
            
            {{-- Info --}}
            <div class="flex-1">
                <h1 class="text-3xl font-bold tracking-tight">{{ $user->pilot_id }}</h1>
                <p class="text-white text-lg">{{ $user->name }}</p>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-[10px] font-bold text-[#10b981] bg-[#10b981]/10 px-2 py-0.5 rounded uppercase tracking-widest">{{ $user->rank->name ?? 'CHIEF PILOT' }}</span>
                    <span class="text-xs text-slate-500">{{ $user->email }}</span>
                </div>
            </div>
            
            {{-- Tabs --}}
            <div class="flex bg-[#0f0f0f] p-1 rounded-lg border border-[#222]">
                @foreach(['Flights', 'Awards', 'Tours', 'Passport', 'Logbook'] as $tab)
                <button wire:click="$set('activeTab', '{{ $tab }}')" class="px-5 py-2 text-xs font-bold uppercase tracking-widest rounded transition-all {{ $activeTab === $tab ? 'bg-[#1a1a1a] text-white' : 'text-slate-500 hover:text-slate-300' }}">
                    {{ $tab }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Stats Bento Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            @foreach(['DAY STREAK' => $stats['dayStreak'], 'BEST STREAK' => $stats['bestStreak'], 'TOTAL HOURS' => number_format($stats['totalHours'], 1), 'FLIGHTS' => $stats['totalFlights'], 'AVG LANDING' => number_format($stats['avgLanding'], 0), 'BALANCE' => '$' . number_format($stats['balance'], 0)] as $label => $value)
            <div class="bg-[#0a0a0a] p-5 rounded-xl border border-[#1a1a1a] hover:border-[#333] transition">
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">{{ $label }}</p>
                <p class="text-2xl font-semibold">{{ $value }}</p>
            </div>
            @endforeach
        </div>

        {{-- Main Content --}}
        @if($activeTab === 'Flights')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data x-init="initCharts(@json($chartData))">
            <div class="bg-[#0a0a0a] p-6 rounded-xl border border-[#1a1a1a]">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">FLIGHT MAP</h2>
                <div id="flightMap" class="h-80 w-full bg-[#050505] rounded border border-[#222]"></div>
            </div>
            <div class="bg-[#0a0a0a] p-6 rounded-xl border border-[#1a1a1a]">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">DESTINATIONS</h2>
                <canvas id="radarChart" class="h-80 w-full"></canvas>
            </div>
        </div>
        @elseif($activeTab === 'Awards')
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @forelse($user->achievements as $achievement)
            <div class="bg-[#0a0a0a] p-5 rounded-xl border border-[#1a1a1a] text-center hover:border-[#333] transition group">
                <div class="text-4xl mb-3">{{ $achievement->icon ?? '🏆' }}</div>
                <p class="text-sm font-bold text-white group-hover:text-[#10b981] transition-colors">{{ $achievement->name }}</p>
                <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">{{ $achievement->description }}</p>
                <p class="text-[9px] text-slate-600 mt-2">{{ \Carbon\Carbon::parse($achievement->pivot->unlocked_at)->format('M d, Y') }}</p>
            </div>
            @empty
            <div class="col-span-full text-center py-16 text-slate-500">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                </svg>
                <p class="text-sm">No achievements unlocked yet</p>
                <p class="text-xs text-slate-600 mt-1">Complete flights to earn achievements</p>
            </div>
            @endforelse
        </div>
        @elseif($activeTab === 'Tours')
        <div class="space-y-4">
            @forelse($user->tours as $tour)
            @php $tp = $tourProgress[$tour->id] ?? ['pct' => 0, 'completed' => false]; @endphp
            <div class="bg-[#0a0a0a] p-5 rounded-xl border border-[#1a1a1a] hover:border-[#333] transition {{ $tp['completed'] ? 'border-[#10b981]/30' : '' }}">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex-1">
                        <h3 class="font-bold text-white">{{ $tour->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $tour->description }}</p>
                    </div>
                    @if($tp['completed'])
                    <span class="shrink-0 text-[10px] font-bold text-[#10b981] bg-[#10b981]/10 px-3 py-1 rounded-full border border-[#10b981]/20">COMPLETED</span>
                    @else
                    <span class="shrink-0 text-[10px] font-bold text-amber-400 bg-amber-400/10 px-3 py-1 rounded-full border border-amber-400/20">{{ $tp['pct'] }}%</span>
                    @endif
                </div>
                @if($tour->waypoints)
                <div class="flex items-center gap-1.5 flex-wrap mb-3">
                    @foreach($tour->waypoints as $i => $wpt)
                    <span class="text-xs font-mono font-bold {{ $i === 0 ? 'text-[#10b981]' : 'text-slate-400' }}">{{ $wpt }}</span>
                    @if($i < count($tour->waypoints) - 1)
                    <svg class="w-3 h-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    @endif
                    @endforeach
                </div>
                @endif
                <div class="mt-3 h-1.5 bg-[#1a1a1a] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $tp['completed'] ? 'bg-[#10b981]' : 'bg-[#10b981]' }}" style="width: {{ $tp['pct'] }}%"></div>
                </div>
                @if($tour->pivot->completed_at)
                <p class="text-[10px] text-slate-600 mt-2">Completed {{ \Carbon\Carbon::parse($tour->pivot->completed_at)->format('M d, Y') }}</p>
                @endif
            </div>
            @empty
            <div class="text-center py-16 text-slate-500">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                <p class="text-sm">No tours joined yet</p>
                <p class="text-xs text-slate-600 mt-1">Join a tour to start your journey</p>
            </div>
            @endforelse
        </div>
        @elseif($activeTab === 'Passport')
        <div class="bg-[#0a0a0a] p-6 rounded-xl border border-[#1a1a1a]">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div class="text-center p-4 bg-[#0f0f0f] rounded-xl border border-[#1a1a1a]">
                    <p class="text-3xl font-bold text-white">{{ $passportStats['uniqueAirports'] }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1">Airports Visited</p>
                </div>
                <div class="text-center p-4 bg-[#0f0f0f] rounded-xl border border-[#1a1a1a]">
                    <p class="text-3xl font-bold text-white">{{ $passportStats['countries'] }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1">Countries</p>
                </div>
                <div class="text-center p-4 bg-[#0f0f0f] rounded-xl border border-[#1a1a1a]">
                    <p class="text-3xl font-bold text-white">{{ $passportStats['mostVisited'] }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1">Most Visited</p>
                </div>
                <div class="text-center p-4 bg-[#0f0f0f] rounded-xl border border-[#1a1a1a]">
                    <p class="text-3xl font-bold text-white">{{ number_format($passportStats['totalDistance']) }} km</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1">Total Distance</p>
                </div>
            </div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Visited Airports</h3>
            @if($passportStats['airports'])
            <div class="flex flex-wrap gap-2">
                @foreach($passportStats['airports'] as $code => $count)
                <span class="text-xs bg-[#1a1a1a] text-slate-300 px-3 py-1.5 rounded-full border border-[#222] hover:border-[#10b981]/30 transition">
                    {{ $code }} <span class="text-slate-600">({{ $count }})</span>
                </span>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-500 text-center py-8">No flights logged yet</p>
            @endif
        </div>
        @elseif($activeTab === 'Logbook')
        <div class="bg-[#0a0a0a] rounded-xl border border-[#1a1a1a] overflow-hidden">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs uppercase bg-[#0f0f0f] text-slate-600">
                    <tr><th class="px-6 py-4">Flight</th><th class="px-6 py-4">Route</th><th class="px-6 py-4">Time</th><th class="px-6 py-4">Date</th></tr>
                </thead>
                <tbody class="divide-y divide-[#1a1a1a]">
                    @foreach($user->pireps()->latest()->paginate(10) as $pirep)
                    <tr class="hover:bg-[#111]">
                        <td class="px-6 py-4 font-bold text-white">{{ $pirep->flight_number }}</td>
                        <td class="px-6 py-4">{{ $pirep->departure }} → {{ $pirep->arrival }}</td>
                        <td class="px-6 py-4">{{ $pirep->flight_time }}h</td>
                        <td class="px-6 py-4">{{ $pirep->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t border-[#1a1a1a]">{{ $user->pireps()->latest()->paginate(10)->links() }}</div>
        </div>
        @endif
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function initCharts(data) {
            if (!document.getElementById('radarChart')) return;
            new Chart(document.getElementById('radarChart'), {
                type: 'radar',
                data: {
                    labels: Object.keys(data.destinations),
                    datasets: [{
                        data: Object.values(data.destinations),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        pointRadius: 0
                    }]
                },
                options: { maintainAspectRatio: false, scales: { r: { grid: { color: '#222' }, angleLines: { color: '#222' }, ticks: { display: false }, pointLabels: { color: '#666' } } } }
            });
            const map = L.map('flightMap', { zoomControl: false });
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; CartoDB', subdomains: 'abcd' }).addTo(map);
            if (data.routes && data.routes.length) {
                const bounds = [];
                data.routes.forEach(function(r) {
                    const from = L.latLng(r.from.lat, r.from.lng);
                    const to = L.latLng(r.to.lat, r.to.lng);
                    L.polyline([from, to], { color: '#ef4444', weight: 1.5, opacity: 0.6 }).addTo(map);
                    L.circleMarker(from, { radius: 3, color: '#10b981', fillOpacity: 0.8 }).addTo(map);
                    L.circleMarker(to, { radius: 3, color: '#10b981', fillOpacity: 0.8 }).addTo(map);
                    bounds.push(from, to);
                });
                map.fitBounds(bounds, { padding: [30, 30] });
            } else {
                map.setView([0, 0], 2);
            }
        }
    </script>
    @endpush
</div>
