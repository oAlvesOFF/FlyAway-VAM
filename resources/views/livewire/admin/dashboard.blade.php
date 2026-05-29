<?php

use App\Models\Achievement;
use App\Models\Aircraft;
use App\Models\News;
use App\Models\Pirep;
use App\Models\Rank;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Notifications\PirepApproved;
use App\Notifications\PirepRejected;
use App\Helpers\ActivityLogger;
use App\Helpers\DiscordNotifier;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $totalPilots = 0;
    public $totalAircraft = 0;
    public $totalSchedules = 0;
    public $pendingPireps = 0;
    public $pendingPilots = 0;
    public $chartData = [];
    public $rejectReason = [];

    public $searchFlight = '';
    public $searchPilot = '';
    public $searchAircraft = '';
    public $filterStatus = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedPireps = [];
    public $selectAll = false;

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        session()->flash('success', 'Cache cleared.');
    }

    public function resetAllMaintenance(): void
    {
        Aircraft::query()->update([
            'last_service_at' => now(),
            'total_hours_since_service' => 0,
        ]);
        session()->flash('success', 'All fleet maintenance reset.');
    }

    public function with(): array
    {
        $query = Pirep::with('user');

        if ($this->searchFlight) {
            $query->where('flight_number', 'like', '%' . $this->searchFlight . '%');
        }
        if ($this->searchPilot) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->searchPilot . '%')
                  ->orWhere('pilot_id', 'like', '%' . $this->searchPilot . '%');
            });
        }
        if ($this->searchAircraft) {
            $query->where('aircraft_registration', 'like', '%' . $this->searchAircraft . '%');
        }
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return ['recentPireps' => $query->latest()->paginate(10)];
    }

    public function updatingSearchFlight(): void { $this->resetPage(); }
    public function updatingSearchPilot(): void { $this->resetPage(); }
    public function updatingSearchAircraft(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }

    public function refreshStats(): void
    {
        $this->totalPilots = User::count();
        $this->totalAircraft = Aircraft::count();
        $this->totalSchedules = Schedule::count();
        $this->pendingPireps = Pirep::where('status', 'pending')->count();
        $this->pendingPilots = User::where('status', 'pending')->count();

        $this->chartData = [
            'pirepStatus' => [
                'approved' => Pirep::where('status', 'approved')->count(),
                'pending' => Pirep::where('status', 'pending')->count(),
                'rejected' => Pirep::where('status', 'rejected')->count(),
            ],
            'weeklyFlights' => Pirep::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(created_at)'))->orderBy('date')->pluck('count', 'date')->toArray(),
            'monthlyHours' => Pirep::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(flight_time) as hours')
                ->where('created_at', '>=', now()->subMonths(6))
                ->where('status', 'approved')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))->orderBy('month')->pluck('hours', 'month')->toArray(),
            'topRoutes' => Pirep::selectRaw('CONCAT(departure, "→", arrival) as route, COUNT(*) as count')
                ->where('status', 'approved')
                ->groupBy(DB::raw('CONCAT(departure, "→", arrival)'))->orderBy('count', 'desc')->take(8)->pluck('count', 'route')->toArray(),
            'pilotRegistrations' => User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))->orderBy('month')->pluck('count', 'month')->toArray(),
            'aircraftCategories' => Aircraft::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')->pluck('count', 'category')->toArray(),
            'fleetUtilization' => Pirep::selectRaw('aircraft_registration, SUM(flight_time) as hours, COUNT(*) as flights')
                ->where('status', 'approved')
                ->groupBy('aircraft_registration')->orderBy('hours', 'desc')->take(10)->get()->toArray(),
            'monthlyAircraftHours' => Pirep::selectRaw('aircraft_registration, DATE_FORMAT(created_at, "%Y-%m") as month, SUM(flight_time) as hours')
                ->where('status', 'approved')
                ->where('created_at', '>=', now()->subMonths(3))
                ->groupBy('aircraft_registration', DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                ->orderBy('month')->get()->toArray(),
        ];
    }

    public $rejectingId = null;
    public $rejectReasonInput = '';

    public function confirmReject($id): void
    {
        $this->rejectingId = $id;
        $this->rejectReasonInput = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectReasonInput = '';
    }

    public function rejectPirep(): void
    {
        $id = $this->rejectingId;
        if (!$id) return;

        $pirep = Pirep::with('user')->find($id);
        if (!$pirep || $pirep->status !== 'pending') return;

        $pirep->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectReasonInput ?: null,
        ]);

        if ($pirep->user) {
            $pirep->user->notify(new PirepRejected($pirep));
        }

        ActivityLogger::log('reject_pirep', 'pirep', $pirep->id, "Rejected PIREP {$pirep->flight_number}: {$pirep->rejection_reason}");
        DiscordNotifier::pirepRejected($pirep->flight_number, $pirep->departure, $pirep->arrival, $pirep->user?->name ?? 'Unknown', $pirep->rejection_reason);

        $this->rejectingId = null;
        $this->rejectReasonInput = '';
        session()->flash('success', "PIREP {$pirep->flight_number} rejected. Pilot notified.");
        $this->refreshStats();
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedPireps = $value ? $this->getPirepIds() : [];
    }

    protected function getPirepIds(): array
    {
        return Pirep::where('status', 'pending')->pluck('id')->toArray();
    }

    public function bulkApprove(): void
    {
        $count = 0;
        foreach ($this->selectedPireps as $id) {
            $pirep = Pirep::with('user')->find($id);
            if (!$pirep || $pirep->status !== 'pending') continue;

            $pirep->update(['status' => 'approved']);
            $this->applyApprovalSideEffects($pirep);
            $count++;
        }
        ActivityLogger::log('bulk_approve_pirep', null, null, "Bulk approved {$count} PIREPs");

        $this->selectedPireps = [];
        $this->selectAll = false;
        session()->flash('success', "Approved {$count} PIREPs.");
        $this->refreshStats();
    }

    public function bulkReject(): void
    {
        $count = 0;
        foreach ($this->selectedPireps as $id) {
            $pirep = Pirep::with('user')->find($id);
            if (!$pirep || $pirep->status !== 'pending') continue;

            $pirep->update([
                'status' => 'rejected',
                'rejection_reason' => 'Bulk rejected by staff.',
            ]);
            if ($pirep->user) {
                $pirep->user->notify(new PirepRejected($pirep));
            }
            $count++;
        }
        $this->selectedPireps = [];
        $this->selectAll = false;
        session()->flash('success', "Rejected {$count} PIREPs.");
        $this->refreshStats();
    }

    protected function applyApprovalSideEffects(Pirep $pirep): void
    {
        $user = $pirep->user;
        if ($user) {
            $user->increment('total_hours', $pirep->flight_time);
            $user->increment('total_flights');
            $user->update(['last_location' => $pirep->arrival]);

            $newRank = Rank::where('minimum_hours', '<=', $user->total_hours)
                ->orderBy('minimum_hours', 'desc')->first();
            if ($newRank) {
                $user->update(['rank_id' => $newRank->id]);
            }

            $user->notify(new PirepApproved($pirep));
            Achievement::checkAndUnlock($user);
        }
        Aircraft::where('registration', $pirep->aircraft_registration)
            ->update([
                'location' => $pirep->arrival,
                'total_hours_since_service' => \DB::raw('total_hours_since_service + ' . $pirep->flight_time),
            ]);
    }

    public function approvePirep($id): void
    {
        $pirep = Pirep::with('user')->find($id);
        if (!$pirep || $pirep->status !== 'pending') return;

        $pirep->update(['status' => 'approved']);
        $this->applyApprovalSideEffects($pirep);

        ActivityLogger::log('approve_pirep', 'pirep', $pirep->id, "Approved PIREP {$pirep->flight_number} ({$pirep->departure}→{$pirep->arrival})");
        DiscordNotifier::pirepApproved($pirep->flight_number, $pirep->departure, $pirep->arrival, $pirep->user?->name ?? 'Unknown', $pirep->score);

        session()->flash('success', "PIREP {$pirep->flight_number} approved. Pilot notified.");
        $this->refreshStats();
    }
}; ?>

<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-7xl mx-auto space-y-6">
    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-2">
        <button wire:click="clearCache" wire:confirm="Clear application cache?" class="btn-secondary text-xs">Clear Cache</button>
        <button wire:click="resetAllMaintenance" wire:confirm="Reset maintenance for ALL aircraft?" class="btn-secondary text-xs">Reset All Maint.</button>
        <a href="{{ route('admin.activity-log') }}" class="btn-secondary text-xs">Activity Log</a>
    </div>

    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Admin Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your virtual airline.</p>
    </div>

    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat-card">
            <span class="stat-label">Pilots</span>
            <span class="stat-value">{{ $totalPilots }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Aircraft</span>
            <span class="stat-value">{{ $totalAircraft }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Schedules</span>
            <span class="stat-value">{{ $totalSchedules }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending PIREPs</span>
            <span class="stat-value text-amber-600 dark:text-amber-400">{{ $pendingPireps }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Pilots</span>
            <span class="stat-value text-crimson-600 dark:text-crimson-400">{{ $pendingPilots }}</span>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">PIREP Status Breakdown</h3>
            <canvas id="pirepChart" height="200"></canvas>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Flights Last 7 Days</h3>
            <canvas id="weeklyChart" height="200"></canvas>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Monthly Flight Hours (6 Months)</h3>
            <canvas id="monthlyHoursChart" height="200"></canvas>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Pilot Registrations</h3>
            <canvas id="registrationsChart" height="200"></canvas>
        </div>
    </div>

    {{-- Charts Row 3 --}}
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Top 8 Routes</h3>
            <canvas id="topRoutesChart" height="200"></canvas>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Aircraft Category Distribution</h3>
            <canvas id="aircraftCategoryChart" height="200"></canvas>
        </div>
    </div>

    {{-- Fleet Utilization --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Fleet Utilization — Top 10 Aircraft</h3>
            <span class="text-xs text-slate-400">Approved PIREPs only</span>
        </div>
        <canvas id="fleetUtilChart" height="200"></canvas>
    </div>

    {{-- Quick Actions --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.fleet') }}" wire:navigate class="card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-white">Fleet Manager</p>
                <p class="text-sm text-slate-500">Manage aircraft registrations</p>
            </div>
        </a>
        <a href="{{ route('admin.schedules') }}" wire:navigate class="card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.251 2.251 0 012.366 1.889m-5.8 0A2.251 2.251 0 0012.75 2.25H12a2.251 2.251 0 00-2.366 1.889"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-white">Schedules</p>
                <p class="text-sm text-slate-500">Manage flight schedules</p>
            </div>
        </a>
        <a href="{{ route('admin.news') }}" wire:navigate class="card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-crimson-100 dark:bg-crimson-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-crimson-600 dark:text-crimson-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-white">News</p>
                <p class="text-sm text-slate-500">Post announcements & updates</p>
            </div>
        </a>
    </div>

    {{-- Rejection Reason Modal --}}
    <div x-data="{ show: @entangle('rejectingId') }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="$wire.cancelReject()">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Reject PIREP</h3>
            <p class="text-sm text-slate-500 mb-4">Provide a reason for rejection (optional). The pilot will see this in their notification.</p>
            <textarea wire:model="rejectReasonInput" rows="3" class="input-field w-full" placeholder="e.g. Incorrect aircraft registration, missing route data..."></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="$wire.cancelReject()" class="btn-secondary text-sm">Cancel</button>
                <button wire:click="rejectPirep" class="btn-danger text-sm">Reject PIREP</button>
            </div>
        </div>
    </div>

    {{-- Recent PIREPs --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">PIREPs</h2>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ $recentPireps->total() }} total</span>
                <a href="{{ route('admin.export.pireps.csv') }}" class="text-xs text-crimson-600 dark:text-crimson-400 hover:underline font-medium">Export CSV</a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 mb-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
            <input wire:model.live.debounce="searchFlight" placeholder="Flight #" class="input-field text-xs px-3 py-1.5 w-28">
            <input wire:model.live.debounce="searchPilot" placeholder="Pilot" class="input-field text-xs px-3 py-1.5 w-32">
            <input wire:model.live.debounce="searchAircraft" placeholder="Aircraft" class="input-field text-xs px-3 py-1.5 w-28">
            <select wire:model.live="filterStatus" class="input-field text-xs px-3 py-1.5 w-28">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input wire:model.live="dateFrom" type="date" class="input-field text-xs px-3 py-1.5 w-36">
            <input wire:model.live="dateTo" type="date" class="input-field text-xs px-3 py-1.5 w-36">
            <button wire:click="$set('searchFlight', ''); $set('searchPilot', ''); $set('searchAircraft', ''); $set('filterStatus', ''); $set('dateFrom', ''); $set('dateTo', '')" class="text-xs text-slate-500 hover:text-crimson-600 dark:hover:text-crimson-400 font-medium">Clear</button>
        </div>

        @if(count($selectedPireps) > 0)
            <div class="flex items-center gap-3 mb-3 p-3 bg-crimson-50 dark:bg-crimson-950/30 rounded-xl">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ count($selectedPireps) }} selected</span>
                <button wire:click="bulkApprove" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline font-medium">Approve All</button>
                <button wire:click="bulkReject" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">Reject All</button>
                <button wire:click="$set('selectedPireps', [])" class="text-sm text-slate-500 hover:underline">Clear</button>
            </div>
        @endif

        @if($recentPireps->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <th class="pb-3 pr-2 w-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600">
                            </th>
                            <th class="pb-3 font-medium">Pilot</th>
                            <th class="pb-3 font-medium">Flight</th>
                            <th class="pb-3 font-medium">Route</th>
                            <th class="pb-3 font-medium">Time</th>
                            <th class="pb-3 font-medium">Score</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ detail: null }">
                        @foreach($recentPireps as $pirep)
                            <tr class="border-b border-slate-100 dark:border-slate-800 {{ in_array($pirep->id, $selectedPireps) ? 'bg-crimson-50/50 dark:bg-crimson-950/20' : '' }}">
                                <td class="py-3 pr-2">
                                    <input type="checkbox" wire:model.live="selectedPireps" value="{{ $pirep->id }}" class="rounded border-slate-300 dark:border-slate-600">
                                </td>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-3 text-slate-900 dark:text-white">{{ $pirep->user?->name }}</td>
                                <td class="py-3 font-medium">{{ $pirep->flight_number }}</td>
                                <td class="py-3 text-slate-500">{{ $pirep->departure }} → {{ $pirep->arrival }}</td>
                                <td class="py-3 text-slate-500">{{ $pirep->flight_time }}h</td>
                                <td class="py-3">{{ $pirep->score }}</td>
                                <td class="py-3">
                                    @if($pirep->status === 'approved')
                                        <span class="badge-success">Approved</span>
                                    @elseif($pirep->status === 'pending')
                                        <span class="badge-warning">Pending</span>
                                    @else
                                        <span class="badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right flex items-center justify-end gap-2">
                                    <button @click="detail = detail === {{ $pirep->id }} ? null : {{ $pirep->id }}" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline font-medium">View</button>
                                    @if($pirep->status === 'pending')
                                        <button wire:click="approvePirep({{ $pirep->id }})" wire:loading.attr="disabled" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline font-medium">Approve</button>
                                        <button wire:click="confirmReject({{ $pirep->id }})" wire:loading.attr="disabled" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">Reject</button>
                                    @endif
                                </td>
                            </tr>
                            <tr x-show="detail === {{ $pirep->id }}" x-cloak>
                                <td colspan="7" class="py-4 px-4 bg-slate-50 dark:bg-slate-800/30 rounded-b-xl">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 text-sm">
                                        <div><span class="text-xs text-slate-400">Aircraft</span><p class="font-medium">{{ $pirep->aircraft_registration }} ({{ $pirep->aircraft_icao }})</p></div>
                                        <div><span class="text-xs text-slate-400">Landing Rate</span><p class="font-medium">{{ $pirep->landing_rate }} fpm</p></div>
                                        <div><span class="text-xs text-slate-400">Submitted</span><p class="font-medium">{{ $pirep->submitted_at ? (\Carbon\Carbon::parse($pirep->submitted_at)->format('d M Y H:i')) : (\Carbon\Carbon::parse($pirep->created_at)->format('d M Y H:i')) }}</p></div>
                                        <div><span class="text-xs text-slate-400">Aircraft Type</span><p class="font-medium">{{ $pirep->aircraft_icao }}</p></div>
                                    </div>
                                    @if($pirep->route)
                                        <div class="mb-2">
                                            <span class="text-xs text-slate-400">Route String</span>
                                            <p class="font-mono text-xs mt-0.5 text-slate-600 dark:text-slate-400">{{ $pirep->route }}</p>
                                        </div>
                                    @endif
                                    @if($pirep->log)
                                        <div>
                                            <span class="text-xs text-slate-400">Flight Log</span>
                                            <pre class="mt-1 text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-900/50 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap max-h-48 overflow-y-auto">{{ $pirep->log }}</pre>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-slate-500 text-sm">No PIREPs yet.</p>
        @endif
        @if($recentPireps->hasPages())
            <div class="mt-4">
                {{ $recentPireps->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    const pirepStatus = @json($chartData['pirepStatus']);
    const weeklyFlights = @json($chartData['weeklyFlights']);

    new Chart(document.getElementById('pirepChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [pirepStatus.approved || 0, pirepStatus.pending || 0, pirepStatus.rejected || 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } }
        }
    });

    const dates = Object.keys(weeklyFlights);
    const counts = Object.values(weeklyFlights);

    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: dates.map(d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Flights',
                data: counts,
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
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    const monthlyHours = @json($chartData['monthlyHours']);
    new Chart(document.getElementById('monthlyHoursChart'), {
        type: 'line',
        data: {
            labels: Object.keys(monthlyHours),
            datasets: [{
                label: 'Hours',
                data: Object.values(monthlyHours).map(v => Number(v).toFixed(1)),
                borderColor: '#e11d48',
                backgroundColor: 'rgba(225, 29, 72, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: '#e11d48',
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

    const registrations = @json($chartData['pilotRegistrations']);
    new Chart(document.getElementById('registrationsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(registrations),
            datasets: [{
                label: 'New Pilots',
                data: Object.values(registrations),
                backgroundColor: '#6366f1',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    const topRoutes = @json($chartData['topRoutes']);
    const routeColors = ['#e11d48', '#f43f5e', '#fb7185', '#fda4af', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
    new Chart(document.getElementById('topRoutesChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(topRoutes),
            datasets: [{
                data: Object.values(topRoutes),
                backgroundColor: routeColors.slice(0, Object.keys(topRoutes).length),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } }
            }
        }
    });

    const acCategories = @json($chartData['aircraftCategories'] ?? []);
    const aircraftCategories = Object.keys(acCategories);
    const categoryCounts = Object.values(acCategories);
    new Chart(document.getElementById('aircraftCategoryChart'), {
        type: 'bar',
        data: {
            labels: aircraftCategories,
            datasets: [{
                label: 'Aircraft',
                data: categoryCounts,
                backgroundColor: ['#e11d48', '#6366f1', '#10b981'],
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } },
                y: { grid: { display: false } }
            }
        }
    });

    const fleetUtil = @json($chartData['fleetUtilization']);
    if (fleetUtil && fleetUtil.length > 0) {
        new Chart(document.getElementById('fleetUtilChart'), {
            type: 'bar',
            data: {
                labels: fleetUtil.map(f => f.aircraft_registration),
                datasets: [
                    {
                        label: 'Hours',
                        data: fleetUtil.map(f => Number(f.hours).toFixed(1)),
                        backgroundColor: '#e11d48',
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Flights',
                        data: fleetUtil.map(f => f.flights),
                        backgroundColor: '#6366f1',
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } } }
                },
                scales: {
                    y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Hours', font: { size: 11 } } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Flights', font: { size: 11 } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
