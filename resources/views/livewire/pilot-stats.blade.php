<?php

use App\Models\Achievement;
use App\Models\Pirep;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public array $monthlyHours = [];
    public array $scoreTrend = [];
    public array $aircraftBreakdown = [];
    public array $topRoutes = [];
    public int $totalFlights = 0;
    public float $totalHours = 0;
    public float $avgScore = 0;
    public int $totalAchievements = 0;
    public int $unlockedAchievements = 0;
    public ?string $worstLanding = null;
    public ?string $bestLanding = null;
    public string $mostFlownAircraft = '—';
    public string $favoriteRoute = '—';

    public function mount(): void
    {
        $userId = auth()->id();

        // Monthly hours (last 12 months)
        $this->monthlyHours = Pirep::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(flight_time) as hours')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->pluck('hours', 'month')
            ->toArray();

        // Score trend (last 20)
        $scores = Pirep::where('user_id', $userId)
            ->where('status', 'approved')
            ->latest()
            ->take(20)
            ->get(['score', 'flight_number', 'created_at']);
        $this->scoreTrend = $scores->toArray();

        // Aircraft breakdown
        $ac = Pirep::selectRaw('aircraft_registration, aircraft_icao, COUNT(*) as flights, SUM(flight_time) as hours')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->groupBy('aircraft_registration', 'aircraft_icao')
            ->orderBy('flights', 'desc')
            ->take(10)
            ->get();
        $this->aircraftBreakdown = $ac->toArray();
        if ($ac->isNotEmpty()) {
            $this->mostFlownAircraft = $ac->first()->aircraft_registration . ' (' . $ac->first()->aircraft_icao . ')';
        }

        // Top routes
        $routes = Pirep::selectRaw('CONCAT(departure, "→", arrival) as route, COUNT(*) as flights')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->groupBy(DB::raw('CONCAT(departure, "→", arrival)'))
            ->orderBy('flights', 'desc')
            ->take(5)
            ->pluck('flights', 'route');
        $this->topRoutes = $routes->toArray();
        if ($routes->isNotEmpty()) {
            $this->favoriteRoute = $routes->keys()->first();
        }

        // Aggregates
        $agg = Pirep::where('user_id', $userId)->where('status', 'approved');
        $this->totalFlights = (clone $agg)->count();
        $this->totalHours = (clone $agg)->sum('flight_time');
        $this->avgScore = (clone $agg)->avg('score') ?? 0;
        $this->bestLanding = (clone $agg)->whereNotNull('landing_rate')->min('landing_rate');
        $this->worstLanding = (clone $agg)->whereNotNull('landing_rate')->max('landing_rate');

        $this->totalAchievements = Achievement::count();
        $this->unlockedAchievements = auth()->user()->achievements()->count();
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Statistics</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detailed breakdown of your flying career.</p>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <span class="stat-label">Total Flights</span>
            <span class="stat-value">{{ $totalFlights }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Hours</span>
            <span class="stat-value">{{ number_format($totalHours, 1) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Average Score</span>
            <span class="stat-value">{{ number_format($avgScore, 0) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Achievements</span>
            <span class="stat-value">{{ $unlockedAchievements }}/{{ $totalAchievements }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Best Landing</p>
            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $bestLanding ?? '—' }} fpm</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Worst Landing</p>
            <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1">{{ $worstLanding ?? '—' }} fpm</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Favorite Aircraft</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1 truncate">{{ $mostFlownAircraft }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Favorite Route</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1 truncate">{{ $favoriteRoute }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Monthly Hours Chart --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Monthly Flight Hours (12mo)</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="monthlyHoursChart"></canvas>
            </div>
        </div>

        {{-- Score Trend --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Score Trend (Last 20)</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="scoreTrendChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Aircraft Breakdown --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Aircraft Usage</h3>
            @if(count($aircraftBreakdown) > 0)
            <div class="space-y-3">
                @foreach($aircraftBreakdown as $ac)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-slate-700 dark:text-slate-300">{{ $ac['aircraft_registration'] }}</span>
                        <span class="text-xs text-slate-400">({{ $ac['aircraft_icao'] }})</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span>{{ $ac['flights'] }} flights</span>
                        <span>{{ number_format($ac['hours'], 1) }}h</span>
                    </div>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-crimson-500 h-1.5 rounded-full" style="width: {{ ($ac['hours'] / max(array_column($aircraftBreakdown, 'hours'))) * 100 }}%"></div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-sm text-slate-400">No data yet.</p>
            @endif
        </div>

        {{-- Top Routes --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Top Routes</h3>
            @if(count($topRoutes) > 0)
            <div class="space-y-3">
                @foreach($topRoutes as $route => $count)
                <div class="flex items-center justify-between text-sm">
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $route }}</span>
                    <span class="text-slate-500">{{ $count }} flights</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ ($count / max($topRoutes)) * 100 }}%"></div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-sm text-slate-400">No data yet.</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    // Monthly Hours Chart
    const mh = @json($monthlyHours);
    if (Object.keys(mh).length > 0) {
        new Chart(document.getElementById('monthlyHoursChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(mh),
                datasets: [{
                    label: 'Hours',
                    data: Object.values(mh).map(v => Number(v).toFixed(1)),
                    backgroundColor: '#e11d48',
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Score Trend
    const scores = @json($scoreTrend);
    if (scores.length > 0) {
        new Chart(document.getElementById('scoreTrendChart'), {
            type: 'line',
            data: {
                labels: scores.map(s => s.flight_number),
                datasets: [{
                    label: 'Score',
                    data: scores.map(s => s.score),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#6366f1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, min: 0, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
