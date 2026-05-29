<?php
use App\Models\User;
use App\Models\Pirep;
use App\Models\Airport;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    public $user;
    public $stats = [];
    public $chartData = [];
    public $passportStats = [];
    public $activeTab = 'Flights';
    public $tourProgress = [];
    public $activityGrid = [];

    public function mount($pilotId): void
    {
        $this->user = User::with(['rank', 'achievements', 'tours'])->findOrFail($pilotId);
        $this->calculateStats();
        $this->prepareChartData();
        $this->computePassportStats();
        $this->computeTourProgress();
        $this->buildActivityGrid();
    }

    public function buildActivityGrid(): void
    {
        $pirepsData = $this->user->pireps()
            ->where('status', 'approved')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $start = now()->subYear()->startOfWeek(Carbon::SUNDAY);
        $end   = now()->endOfWeek(Carbon::SATURDAY);

        $weeks = [];
        $currentWeek = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $currentWeek[] = [
                'date'  => $key,
                'count' => $pirepsData[$key] ?? 0,
            ];
            if (count($currentWeek) == 7) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }
        if (count($currentWeek) > 0) {
            while(count($currentWeek) < 7) {
                $currentWeek[] = ['date' => '', 'count' => 0];
            }
            $weeks[] = $currentWeek;
        }
        $this->activityGrid = $weeks;
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
            $segments  = count($waypoints) - 1;
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

        $dayStreak  = 0;
        $bestStreak = 0;
        $currentRun = 0;
        $prevDate   = null;

        foreach ($dates as $date) {
            if ($prevDate === null) {
                $currentRun = 1;
            } else {
                $diff       = $prevDate->diffInDays($date);
                $currentRun = $diff === 1 ? $currentRun + 1 : 1;
            }
            $bestStreak = max($bestStreak, $currentRun);
            $prevDate   = $date;
        }

        if ($dates->isNotEmpty()) {
            $mostRecent = $dates->first();
            $dayStreak  = $mostRecent->diffInDays(now()->startOfDay()) <= 1 ? $currentRun : 0;
        }

        $this->stats = [
            'totalHours'   => $this->user->total_hours,
            'totalFlights' => $totalFlights,
            'avgLanding'   => (int)($pirepsQuery->avg('landing_rate') ?? 0),
            'dayStreak'    => $dayStreak,
            'bestStreak'   => $bestStreak,
            'balance'      => 16.4, // mock balance
            'joined'       => $this->user->created_at->diffForHumans(['parts' => 1]),
            'flightsYear'  => $pirepsQuery->where('created_at', '>=', now()->subYear())->count(),
        ];
    }

    public function prepareChartData(): void
    {
        $pireps = $this->user->pireps()->where('status', 'approved')->get();
        $airportCodes = $pireps->pluck('arrival')->merge($pireps->pluck('departure'))->unique();
        $airports     = Airport::whereIn('icao', $airportCodes)->get()->keyBy('icao');

        $destinations = [];
        $routes       = [];

        foreach ($pireps as $pirep) {
            $airport = $airports->get($pirep->arrival);
            $country = $airport?->country ?? 'Unknown';
            $destinations[$country] = ($destinations[$country] ?? 0) + 1;

            $dep = $airports->get($pirep->departure);
            $arr = $airports->get($pirep->arrival);
            if ($dep && $arr) {
                $routes[] = [
                    'from' => ['lat' => $dep->lat, 'lng' => $dep->lng],
                    'to'   => ['lat' => $arr->lat, 'lng' => $arr->lng],
                ];
            }
        }
        
        arsort($destinations);
        $destinations = array_slice($destinations, 0, 6);
        if(count($destinations) < 6) {
             $destinations = array_merge($destinations, array_fill_keys(['Europe','Americas','Antarctica','Africa','Oceania','Asia'], 0));
        }

        $this->chartData = [
            'destinations' => $destinations,
            'routes'       => $routes,
        ];
    }

    public function computePassportStats(): void
    {
        $pireps = $this->user->pireps()->where('status', 'approved')->get();
        $codes     = $pireps->pluck('arrival')->merge($pireps->pluck('departure'))->unique();
        $airports  = Airport::whereIn('icao', $codes)->get()->keyBy('icao');
        $visitedAirports = [];
        $countries       = [];
        $totalDistance   = 0;

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
                $totalDistance += $this->haversineDistance($dep->lat, $dep->lng, $arr->lat, $arr->lng);
            }
        }
        $mostVisited = $visitedAirports ? array_search(max($visitedAirports), $visitedAirports) : 'N/A';
        $this->passportStats = [
            'uniqueAirports' => count($visitedAirports),
            'countries'      => count($countries),
            'mostVisited'    => $mostVisited,
            'totalDistance'  => round($totalDistance),
            'airports'       => $visitedAirports,
        ];
    }

    private function haversineDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371;
        $dLat        = deg2rad((float) $lat2 - (float) $lat1);
        $dLng        = deg2rad((float) $lng2 - (float) $lng1);
        $a           = sin($dLat / 2) ** 2 + cos(deg2rad((float) $lat1)) * cos(deg2rad((float) $lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}; ?>

<div class="pp-container" x-data="{ activeTab: @entangle('activeTab') }">

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Base Container */
        .pp-container {
            min-height: 100vh;
            background-color: #050507; /* Very dark background to match image */
            color: #ffffff;
            font-family: 'Inter', system-ui, sans-serif;
            padding: 24px;
        }

        /* SVG Icons */
        .icon-sm { width: 14px; height: 14px; display: inline-block; }
        .icon-md { width: 16px; height: 16px; display: inline-block; }
        .icon-lg { width: 24px; height: 24px; display: inline-block; }

        /* Custom Cards */
        .custom-card {
            background-color: #0a0a0c;
            border: 1px solid #1f1f22;
            border-radius: 8px;
        }
        .card-header-title {
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Layouts */
        .row-split {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-top: 24px;
        }
        @media (min-width: 1024px) {
            .row-split { flex-direction: row; }
        }
        .col-left {
            width: 100%;
        }
        @media (min-width: 1024px) {
            .col-left { width: 320px; flex-shrink: 0; }
        }
        .col-right {
            flex: 1;
            min-width: 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            row-gap: 32px;
        }
        .stat-val {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .stat-val-red {
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .stat-lbl {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
            margin-top: 6px;
        }

        /* Heatmap */
        .heat-cell {
            width: 11px; height: 11px;
            border-radius: 2px;
            background: #1c1c1c;
            flex-shrink: 0;
            display: inline-block;
        }
        .heat-cell[data-level="1"] { background: #5c0b0b; }
        .heat-cell[data-level="2"] { background: #8b0e0e; }
        .heat-cell[data-level="3"] { background: #b91c1c; }
        .heat-cell[data-level="4"] { background: #ef4444; }

        /* Tabs */
        .c-tab {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 0;
            margin-right: 24px;
            color: #888;
            font-size: 12px;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            border-top: none; border-left: none; border-right: none;
        }
        .c-tab.active {
            color: #fff;
            border-bottom-color: #ef4444;
        }
        .c-tab:hover:not(.active) { color: #ccc; }

        /* Table */
        .pp-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .pp-table {
            width: 100%;
            border-collapse: collapse;
        }
        .pp-table th {
            font-size: 9px;
            font-weight: 700;
            color: #888;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 1px solid #1f1f22;
            background: #0a0a0c;
            text-align: left;
        }
        .pp-table td {
            font-size: 11px;
            padding: 12px 16px;
            border-bottom: 1px solid #1f1f22;
        }
        .pp-table tr:hover { background: #111113; }

        /* Map */
        .leaflet-container { background: #0b0b0c !important; }
        .leaflet-bar a { background-color: #1a1a1c !important; color: #888 !important; border-color: #333 !important; }
        .leaflet-bar a:hover { background-color: #2a2a2c !important; color: #fff !important; }

        .map-stats-strip {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #0a0a0c;
            border-top: 1px solid #1f1f22;
            padding: 16px;
            text-align: center;
        }
        @media (min-width: 768px) {
            .map-stats-strip { grid-template-columns: repeat(7, 1fr); }
        }

        /* Bottom Charts Grid */
        .bottom-charts {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            margin-top: 24px;
        }
        @media (min-width: 768px) { .bottom-charts { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1280px) { .bottom-charts { grid-template-columns: repeat(4, 1fr); } }

        .chart-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 128px;
        }
        .chart-circle {
            position: absolute;
            width: 128px;
            height: 128px;
            border-radius: 50%;
        }
        .chart-text {
            position: relative;
            z-index: 10;
            text-align: center;
        }
    </style>
    @endpush

    {{-- HEADER --}}
    <div style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 64px; height: 64px; border-radius: 8px; background-color: #2a1111; display: flex; align-items: center; justify-content: center; color: #d9534f; font-size: 20px; font-weight: bold; border: 1px solid #331111;">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h1 style="font-size: 20px; font-weight: bold; color: #fff; margin: 0;">
                        {{ $user->pilot_id ?? 'CID' }} | {{ $user->name }}
                    </h1>
                    @if($user->status === 'active')
                        <span style="background-color: #10b981; color: #fff; font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: 4px;">Active</span>
                    @else
                        <span style="background-color: #f59e0b; color: #fff; font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: 4px;">{{ ucfirst($user->status) }}</span>
                    @endif
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                    <svg class="icon-sm" style="color: #ef4444;" viewBox="0 0 24 24" fill="currentColor"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                    <svg class="icon-sm" style="color: #6b7280;" viewBox="0 0 24 24" fill="currentColor"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                    <span style="font-size: 12px; color: #9ca3af;">{{ $user->rank?->name ?? 'Pilot' }}</span>
                </div>
            </div>
        </div>

        {{-- TABS --}}
        <div style="display: flex; border-bottom: 1px solid #1f1f22; margin-top: 24px; overflow-x: auto;">
            <button wire:click="$set('activeTab', 'Flights')" class="c-tab {{ $activeTab === 'Flights' ? 'active' : '' }}">
                <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z"/></svg> 
                Flights
            </button>
            <button wire:click="$set('activeTab', 'Awards')" class="c-tab {{ $activeTab === 'Awards' ? 'active' : '' }}">
                <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                Awards <span style="background: #1a1a1a; color: #9ca3af; font-size: 10px; padding: 2px 6px; border-radius: 4px;">{{ count($user->achievements) }}</span>
            </button>
            <button wire:click="$set('activeTab', 'Tours')" class="c-tab {{ $activeTab === 'Tours' ? 'active' : '' }}">
                <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.25 10.5a7.25 7.25 0 1114.5 0 7.25 7.25 0 01-14.5 0z M9.75 14.5a4.25 4.25 0 118.5 0 4.25 4.25 0 01-8.5 0z"/></svg>
                Tours
            </button>
            <button wire:click="$set('activeTab', 'Passport')" class="c-tab {{ $activeTab === 'Passport' ? 'active' : '' }}">
                <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-4m-8-2l8-8m0 0v4m0-4h-4"/></svg>
                Passport
            </button>
            <button wire:click="$set('activeTab', 'Logbook')" class="c-tab {{ $activeTab === 'Logbook' ? 'active' : '' }}">
                <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25v14.25"/></svg>
                Logbook
            </button>
        </div>
    </div>

    {{-- FLIGHTS TAB CONTENT --}}
    @if($activeTab === 'Flights')
    <div>
        {{-- STATS & HEATMAP ROW --}}
        <div class="row-split">
            {{-- Left Stats --}}
            <div class="col-left custom-card" style="padding: 32px 24px; display: flex; align-items: center;">
                <div class="stats-grid" style="width: 100%;">
                    <div style="text-align: center;">
                        <div class="stat-val stat-val-red">
                            <svg class="icon-md" viewBox="0 0 24 24" fill="currentColor"><path d="M17.66 11.2c-.23-.3-.51-.59-.77-.82-.67-.6-1.43-1.03-2.07-1.66C13.33 7.26 13 4.85 13.95 3c-.95.23-1.78.75-2.49 1.32-2.59 2.08-3.61 5.75-2.39 8.9.04.1.08.2.08.33 0 .22-.15.42-.35.5-.23.1-.47.04-.66-.12a7.33 7.33 0 01-1.62-2.46c-1.31 2.37-1.12 5.43.37 7.62 1.11 1.63 2.8 2.65 4.67 2.91 2.45.34 5.09-.43 6.64-2.43 1.25-1.62 1.61-3.8.8-5.6-.08-.19-.18-.39-.34-.58z"/></svg>
                            {{ $stats['dayStreak'] }}
                        </div>
                        <div class="stat-lbl">Day Streak</div>
                    </div>
                    <div style="text-align: center;">
                        <div class="stat-val">{{ $stats['bestStreak'] }}</div>
                        <div class="stat-lbl">Best Streak</div>
                    </div>
                    <div style="text-align: center;">
                        <div class="stat-val">{{ number_format($stats['totalHours']) }}h <span style="font-size:12px; font-weight:normal; color:#aaa;">{{ str_pad((int)(($stats['totalHours'] - (int)$stats['totalHours']) * 60), 2, '0', STR_PAD_LEFT) }}m</span></div>
                        <div class="stat-lbl">Total Hours</div>
                    </div>
                    <div style="text-align: center;">
                        <div class="stat-val">{{ number_format($stats['totalFlights']) }}</div>
                        <div class="stat-lbl">Total Flights</div>
                    </div>
                    <div style="text-align: center;">
                        <div class="stat-val">{{ $stats['avgLanding'] }}</div>
                        <div class="stat-lbl">Avg Landing</div>
                    </div>
                    <div style="text-align: center;">
                        <div class="stat-val">${{ $stats['balance'] }}M</div>
                        <div class="stat-lbl">Balance</div>
                    </div>
                </div>
            </div>

            {{-- Heatmap --}}
            <div class="col-right custom-card" style="padding: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                    <h2 style="font-size: 13px; font-weight: 600; color: #fff; margin: 0;">Flight Activity</h2>
                    <select style="background: transparent; border: 1px solid #333; color: #9ca3af; font-size: 11px; padding: 4px 8px; border-radius: 4px; outline: none;">
                        <option>Last 12 months</option>
                    </select>
                </div>

                {{-- Activity Grid --}}
                <div style="overflow-x: auto; padding-bottom: 8px;">
                    <div style="display: flex; flex-direction: column; gap: 4px; min-width: max-content;">
                        <div style="display: flex; gap: 4px; font-size: 10px; color: #6b7280; padding-left: 20px; margin-bottom: 4px;">
                            @php $months = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May']; @endphp
                            @foreach($months as $m)
                                <div style="width: 44px;">{{ $m }}</div>
                            @endforeach
                        </div>
                        @php $maxVal = max(array_map(function($w){ return max(array_column($w,'count')?:[0]); }, $activityGrid)) ?: 1; @endphp
                        @for($i = 0; $i < 7; $i++)
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <div style="width: 16px; font-size: 10px; color: #6b7280;">
                                    {{ $i==1 ? 'Mon' : ($i==3 ? 'Wed' : ($i==5 ? 'Fri' : '')) }}
                                </div>
                                @foreach($activityGrid as $week)
                                    @php
                                        $day = $week[$i] ?? null;
                                        $lvl = 0;
                                        if($day && $day['count'] > 0) {
                                            $pct = $day['count'] / $maxVal;
                                            $lvl = $pct <= .25 ? 1 : ($pct <= .5 ? 2 : ($pct <= .75 ? 3 : 4));
                                        }
                                    @endphp
                                    <div class="heat-cell" data-level="{{ $lvl }}" title="{{ $day['date'] ?? '' }}: {{ $day['count'] ?? 0 }}"></div>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px;">
                    <div style="font-size: 10px; color: #6b7280;">
                        <p style="margin: 0;">{{ $stats['flightsYear'] }} flights in the last year</p>
                        <p style="margin: 4px 0 0 0; display: flex; align-items: center; gap: 4px;">
                            <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> 
                            Joined {{ $stats['joined'] }} ago
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 10px; color: #6b7280;">
                        <span>Less</span>
                        <div class="heat-cell" data-level="0"></div>
                        <div class="heat-cell" data-level="1"></div>
                        <div class="heat-cell" data-level="2"></div>
                        <div class="heat-cell" data-level="3"></div>
                        <div class="heat-cell" data-level="4"></div>
                        <span>More</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAP & RADAR ROW --}}
        <div class="row-split" x-data x-init="initMapAndRadar(@json($chartData))">
            {{-- Map --}}
            <div class="col-right custom-card" style="display: flex; flex-direction: column; overflow: hidden;">
                <div class="card-header-title" style="padding: 16px; border-bottom: 1px solid #1f1f22;">Flight Map</div>
                <div style="flex: 1; min-height: 350px; position: relative; background: #0b0b0c;">
                    <div id="flightMap" style="position: absolute; inset: 0;"></div>
                </div>
                {{-- Stats strip below map --}}
                @php $lastFlight = $user->pireps()->latest()->first(); @endphp
                <div class="map-stats-strip">
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Hub</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">YBBN</div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Location</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">{{ $lastFlight?->arrival ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Total Revenue</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">$271.2M</div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Distance</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">3.7M nm</div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Passengers</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">1M</div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Last Flight</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">{{ $lastFlight ? $lastFlight->departure.' -> '.$lastFlight->arrival : 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: center; color: #6b7280; margin-bottom: 4px;"><svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg></div>
                        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">VATSIM</div>
                        <div style="font-size: 12px; font-weight: bold; color: #fff;">1259782</div>
                    </div>
                </div>
            </div>

            {{-- Radar --}}
            <div class="col-left custom-card" style="padding: 24px; display: flex; flex-direction: column;">
                <div class="card-header-title" style="margin-bottom: 24px;">Destinations</div>
                <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                    <canvas id="radarChart" style="max-width: 100%; max-height: 250px;"></canvas>
                </div>
                <div style="margin-top: 24px; text-align: center; font-size: 10px; color: #6b7280; line-height: 1.6;">
                    {{ number_format($stats['totalFlights']) }} flights across {{ count($chartData['destinations']) }} regions<br>
                    <span style="color: #fff;">{{ array_key_first($chartData['destinations']) ?: 'None' }}</span> is your top destination
                </div>
            </div>
        </div>

        {{-- TABLE ROW --}}
        <div class="custom-card pp-table-container" style="margin-top: 24px;">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>Airline</th>
                        <th>Flight #</th>
                        <th>Aircraft</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>FPM</th>
                        <th>Revenue</th>
                        <th><div style="display: flex; align-items: center; gap: 4px;">Filed <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div></th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $flights = $user->pireps()->latest()->paginate(10);
                        $mockRev = 207085;
                    @endphp
                    @foreach($flights as $f)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="color: #ef4444; font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 2px;">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg> 
                                    Q<span style="font-size: 9px; margin-top: 2px;">EVENTS</span>
                                </div>
                            </div>
                        </td>
                        <td style="color: #fff;">{{ $f->flight_number }}</td>
                        <td style="color: #9ca3af;">{{ $f->aircraft_icao ?: 'QV-001' }}</td>
                        <td style="color: #fff;">{{ $f->departure }}</td>
                        <td style="color: #fff;">{{ $f->arrival }}</td>
                        <td style="color: #9ca3af;">{{ $f->landing_rate ? $f->landing_rate.'fpm' : '-' }}</td>
                        <td style="color: #10b981;">${{ number_format($mockRev - rand(1000, 50000)) }}</td>
                        <td style="color: #9ca3af;">{{ $f->created_at->diffForHumans() }}</td>
                        <td>
                            <button style="background: #1a1a1c; border: 1px solid #333; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; cursor: pointer;">VIEW</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #6b7280;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    Showing {{ $flights->firstItem() ?? 0 }} to {{ $flights->lastItem() ?? 0 }} of {{ $flights->total() }} flights
                    <div style="display: flex; align-items: center; gap: 8px; margin-left: 16px;">
                        Per page: 
                        <select style="background: transparent; border: 1px solid #333; color: #9ca3af; border-radius: 4px; padding: 2px 8px; outline: none;">
                            <option>10</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button style="background: transparent; border: 1px solid #333; color: #888; padding: 4px 12px; border-radius: 4px; font-size: 11px; cursor: pointer;" {{ $flights->onFirstPage() ? 'disabled' : '' }} wire:click="previousPage">Previous</button>
                    <button style="background: #1a1a1c; border: 1px solid #333; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 11px; cursor: pointer;" {{ !$flights->hasMorePages() ? 'disabled' : '' }} wire:click="nextPage">Next</button>
                </div>
            </div>
        </div>

        {{-- BOTTOM CHARTS ROW --}}
        <div class="bottom-charts">
            <div class="custom-card" style="padding: 24px;">
                <div class="card-header-title" style="margin-bottom: 24px;">Top Airlines</div>
                <div class="chart-wrapper">
                    <div class="chart-circle" style="border: 12px solid #ef4444; border-top-color: #8b0e0e; border-right-color: #5c0b0b;"></div>
                    <div class="chart-text">
                        <div style="font-size: 20px; font-weight: bold; color: #fff;">2K</div>
                        <div style="font-size: 10px; color: #6b7280;">Flights</div>
                    </div>
                </div>
            </div>
            <div class="custom-card" style="padding: 24px;">
                <div class="card-header-title" style="margin-bottom: 24px;">Top Aircraft</div>
                <div class="chart-wrapper">
                    <div class="chart-circle" style="border: 12px solid #ef4444; border-top-color: #333; border-right-color: #5c0b0b;"></div>
                    <div class="chart-text">
                        <div style="font-size: 20px; font-weight: bold; color: #fff;">2K</div>
                        <div style="font-size: 10px; color: #6b7280;">Flights</div>
                    </div>
                </div>
            </div>
            <div class="custom-card" style="padding: 24px;">
                <div class="card-header-title" style="margin-bottom: 24px;">Landings</div>
                <div class="chart-wrapper" style="overflow: hidden;">
                    <div class="chart-circle" style="top: 0; border: 12px solid #333; border-top-color: transparent; border-bottom-color: transparent; border-left-color: #ef4444; border-right-color: #ef4444; transform: rotate(-45deg);"></div>
                    <div class="chart-text" style="background: #000; border: 1px solid #333; padding: 4px 8px; border-radius: 4px; margin-top: 32px;">
                        <div style="font-size: 9px; color: #9ca3af;">0-100 fpm <span style="color: #fff; margin-left: 4px; font-weight: bold;">396</span></div>
                        <div style="font-size: 9px; color: #6b7280;">Landings</div>
                    </div>
                </div>
            </div>
            <div class="custom-card" style="padding: 24px;">
                <div class="card-header-title" style="margin-bottom: 24px;">Flight Duration</div>
                <div class="chart-wrapper">
                    <div class="chart-circle" style="border: 12px solid #ef4444; border-bottom-color: #888; border-left-color: #333;"></div>
                    <div class="chart-text">
                        <div style="font-size: 20px; font-weight: bold; color: #fff;">2K</div>
                        <div style="font-size: 10px; color: #6b7280;">Flights</div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div style="margin-top: 32px; text-align: center; color: #6b7280; font-size: 14px;">
            Content for {{ $activeTab }} tab
        </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    function initMapAndRadar(data) {
        // Radar
        const ctx = document.getElementById('radarChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: Object.keys(data.destinations).map(l => l.padEnd(8,' ')),
                    datasets: [{
                        data: Object.values(data.destinations),
                        backgroundColor: 'rgba(239,68,68,0.8)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        r: {
                            grid: { color: '#222' },
                            angleLines: { color: '#222' },
                            ticks: { display: false },
                            pointLabels: { color: '#666', font: { size: 9 } }
                        }
                    }
                }
            });
        }

        // Map
        const mapEl = document.getElementById('flightMap');
        if (mapEl && typeof L !== 'undefined') {
            const map = L.map('flightMap', { zoomControl: false }).setView([20, 0], 2);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            if (data.routes && data.routes.length) {
                const bounds = [];
                data.routes.forEach(function(r) {
                    const from = L.latLng(r.from.lat, r.from.lng);
                    const to   = L.latLng(r.to.lat, r.to.lng);
                    L.polyline([from, to], { color: '#ef4444', weight: 1.5, opacity: 0.8 }).addTo(map);
                    bounds.push(from, to);
                });
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        }
    }
    
    document.addEventListener('livewire:initialized', () => {
        if(document.getElementById('radarChart')) {
            initMapAndRadar(@json($chartData));
        }
    });
    </script>
    @endpush
</div>
