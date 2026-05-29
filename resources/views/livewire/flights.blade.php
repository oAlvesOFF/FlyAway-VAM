<?php

use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Schedule;
use Livewire\Volt\Component;

new class extends Component {
    public $schedules = [];
    public $aircraftOptions = [];
    public $searchDeparture = '';
    public $searchArrival = '';
    public $searchType = '';
    public $searchDuration = '';

    public function mount(): void
    {
        $this->aircraftOptions = Aircraft::where('status', 'active')->select('icao', 'category')->distinct()->get()->pluck('icao')->unique()->sort()->values()->toArray();
        $this->loadSchedules();
    }

    public function loadSchedules(): void
    {
        $query = Schedule::query();

        if ($this->searchDeparture) {
            $query->where('departure', 'like', strtoupper($this->searchDeparture) . '%');
        }
        if ($this->searchArrival) {
            $query->where('arrival', 'like', strtoupper($this->searchArrival) . '%');
        }
        if ($this->searchType) {
            $query->where('aircraft_type', $this->searchType);
        }
        if ($this->searchDuration) {
            $query->where('flight_time', '<=', (float) $this->searchDuration);
        }

        $this->schedules = $query->orderBy('flight_number')->get();
    }

    public function search(): void
    {
        $this->loadSchedules();
    }

    public function resetSearch(): void
    {
        $this->searchDeparture = '';
        $this->searchArrival = '';
        $this->searchType = '';
        $this->searchDuration = '';
        $this->loadSchedules();
    }

    public function book($scheduleId): void
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $userRank = auth()->user()->rank;

        // Resolve the aircraft category (e.g. "B738" -> "Narrowbody") via the aircraft table
        $categoryAircraft = Aircraft::where('icao', $schedule->aircraft_type)
            ->where('status', 'active')
            ->first();
        $scheduleCategory = $categoryAircraft?->category ?? $schedule->aircraft_type;

        if ($userRank && $userRank->allowed_categories) {
            $allowed = explode(',', $userRank->allowed_categories);
            if (!in_array($scheduleCategory, $allowed)) {
                session()->flash('error', "Your rank ({$userRank->name}) does not permit {$scheduleCategory} aircraft.");
                return;
            }
        }

        $aircraft = Aircraft::where('category', $scheduleCategory)
            ->where('location', $schedule->departure)
            ->where('status', 'active')
            ->first();

        if (!$aircraft) {
            $aircraft = Aircraft::where('category', $scheduleCategory)
                ->where('status', 'active')
                ->first();
        }

        if (!$aircraft) {
            session()->flash('error', 'No available aircraft for this flight.');
            return;
        }

        $existingBid = Bid::where('user_id', auth()->id())
            ->where('schedule_id', $scheduleId)
            ->first();

        if ($existingBid) {
            session()->flash('error', 'You already have a booking for this flight.');
            return;
        }

        Bid::create([
            'user_id' => auth()->id(),
            'schedule_id' => $schedule->id,
            'aircraft_id' => $aircraft->id,
        ]);

        session()->flash('success', "Flight {$schedule->flight_number} booked successfully! Aircraft: {$aircraft->registration}");
        $this->loadSchedules();
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Flight Booking</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Search and book available flights.</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 p-4 text-red-700 dark:text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Search Filters --}}
    <div class="card p-5">
        <div class="grid md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">From</label>
                <input wire:model.live.debounce="searchDeparture" class="input-field" placeholder="YSSY" maxlength="4">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">To</label>
                <input wire:model.live.debounce="searchArrival" class="input-field" placeholder="YMML" maxlength="4">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Aircraft</label>
                <select wire:model.live="searchType" class="input-field">
                    <option value="">All Types</option>
                    @foreach($aircraftOptions as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Max Duration (hrs)</label>
                <input wire:model.live.debounce="searchDuration" class="input-field" type="number" step="0.5" placeholder="5">
            </div>
            <div class="flex items-end gap-2">
                <button wire:click="search" class="btn-primary flex-1">Search</button>
                <button wire:click="resetSearch" class="btn-secondary">Clear</button>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="grid gap-4">
        @forelse($schedules as $schedule)
            <div class="card-hover p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $schedule->departure }}</p>
                            <p class="text-xs text-slate-500">{{ $schedule->departure_time }}</p>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-xs font-medium text-slate-400">{{ $schedule->flight_number }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-px bg-slate-300 dark:bg-slate-600"></div>
                                <svg class="w-4 h-4 text-crimson-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                </svg>
                                <div class="w-12 h-px bg-slate-300 dark:bg-slate-600"></div>
                            </div>
                            <span class="text-xs text-slate-400">{{ $schedule->flight_time }}h</span>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $schedule->arrival }}</p>
                            <p class="text-xs text-slate-500">---</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right text-sm">
                            <span class="badge-info">{{ $schedule->aircraft_type }}</span>
                            <p class="text-xs text-slate-500 mt-1">{{ number_format($schedule->altitude) }}ft</p>
                        </div>
                        <button wire:click="book({{ $schedule->id }})" wire:confirm="Book flight {{ $schedule->flight_number }} from {{ $schedule->departure }} to {{ $schedule->arrival }}?" wire:loading.attr="disabled" class="btn-primary flex items-center gap-2">
                            <svg wire:loading wire:target="book" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span wire:loading.remove wire:target="book">Book</span>
                            <span wire:loading wire:target="book">Booking...</span>
                        </button>
                    </div>
                </div>
                @if($schedule->route)
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-xs text-slate-400">Route: <span class="font-mono">{{ $schedule->route }}</span></p>
                    </div>
                @endif
            </div>
        @empty
            <div class="card p-12 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                </svg>
                <p class="text-slate-500 dark:text-slate-400">No flights found matching your criteria.</p>
            </div>
        @endforelse
    </div>
</div>
