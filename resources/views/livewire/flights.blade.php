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

@push('styles')
<style>
/* ══ Flights Board — Dynamic Theme Colors ══════════════════════ */
:root {
    --bg-page: #f8f9fa;
    --bg-card: #ffffff;
    --bg-header: #f1f3f5;
    --border-card: #e9ebec;
    --text-primary: #212529;
    --text-secondary: #495057;
    --text-muted: #868e96;
    --bg-badge: rgba(81, 140, 229, 0.1);
    --border-badge: rgba(81, 140, 229, 0.25);
    --text-badge: #518ce5;
    --bg-search: #ffffff;
    --border-search: #ced4da;
    --text-search: #212529;
    --shadow-card: 0 2px 4px rgba(0, 0, 0, .04);
}

.dark {
    --bg-page: #1a1d2e;
    --bg-card: #2b2f3e;
    --bg-header: #23263a;
    --border-card: #3a3f54;
    --text-primary: #e2e8f0;
    --text-secondary: #a0a8c0;
    --text-muted: #6b7280;
    --bg-badge: rgba(81, 140, 229, 0.18);
    --border-badge: rgba(81, 140, 229, 0.35);
    --text-badge: #518ce5;
    --bg-search: #23263a;
    --border-search: #3a3f54;
    --text-search: #e2e8f0;
    --shadow-card: none;
}

body, main { background: var(--bg-page) !important; }

.fl-wrap {
    padding: 24px;
    max-width: 1300px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
}

.fl-title {
    font-size: 16px; font-weight: 700; color: var(--text-primary);
    display: flex; align-items: center; gap: 8px; margin-bottom: 24px;
}
.fl-title i { color: var(--text-muted); }

/* Flash Messages */
.fl-alert {
    padding: 14px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;
    display: flex; align-items: center; gap: 8px; margin-bottom: 20px;
}
.fl-alert.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #10b981; }
.fl-alert.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; }

/* Filter Container */
.fl-filters {
    background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 8px;
    padding: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;
    margin-bottom: 24px; box-shadow: var(--shadow-card);
}
.fl-form-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
.fl-input {
    width: 100%; background: var(--bg-search); border: 1px solid var(--border-search);
    color: var(--text-search); padding: 8px 12px; border-radius: 6px; font-size: 13px; outline: none; transition: border-color .2s;
}
.fl-input:focus { border-color: var(--text-badge); }
.fl-btn-group { display: flex; gap: 8px; align-items: flex-end; }
.fl-btn {
    flex: 1; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s; border: none; text-align: center;
}
.fl-btn.primary { background: var(--text-badge); color: #fff; }
.fl-btn.primary:hover { opacity: 0.9; }
.fl-btn.secondary { background: var(--bg-header); border: 1px solid var(--border-card); color: var(--text-primary); }
.fl-btn.secondary:hover { border-color: var(--text-muted); }

/* Table Headers (Fake) */
.fl-headers {
    display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;
    padding: 0 16px 12px; border-bottom: 2px solid var(--text-badge);
    margin-bottom: 16px; font-size: 12px; font-weight: 700; color: var(--text-badge);
}
.fl-headers > div { text-align: center; }

/* Flight Card */
.fl-card {
    background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 8px;
    margin-bottom: 16px; box-shadow: var(--shadow-card);
}

.fl-card-body {
    padding: 20px; display: grid; grid-template-columns: 1fr 1.5fr 1fr; gap: 16px;
    align-items: center; border-bottom: 1px solid var(--border-card);
}

/* Column 1: Flight Details */
.fl-col-details { text-align: center; }
.fl-flight-num { font-size: 15px; font-weight: 800; color: var(--text-primary); }
.fl-flight-lbl { font-size: 11px; color: var(--text-muted); margin-bottom: 8px; }
.fl-aircraft { font-size: 13px; font-weight: 600; color: var(--text-secondary); }

/* Column 2: Route Display */
.fl-col-route { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.fl-airport { text-align: center; width: 120px; }
.fl-airport-icao { font-size: 18px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; justify-content: center; gap: 6px; }
.fl-airport-icao i { font-size: 14px; color: var(--text-badge); }
.fl-airport-time { font-size: 12px; font-weight: 600; color: var(--text-muted); margin: 4px 0 8px; }
.fl-airport-btn {
    display: inline-block; background: var(--text-badge); color: #fff;
    padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 600;
    max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.fl-path { flex: 1; display: flex; align-items: center; position: relative; }
.fl-path-line { flex: 1; border-top: 2px dashed var(--border-card); }
.fl-path-plane {
    width: 32px; height: 32px; border-radius: 50%; background: var(--bg-header); border: 2px solid var(--text-badge);
    display: flex; align-items: center; justify-content: center; color: var(--text-badge); font-size: 16px;
    margin: 0 8px;
}

/* Column 3: Distance / Time */
.fl-col-stats { text-align: center; }
.fl-stat-val { font-size: 14px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; justify-content: center; gap: 6px; }
.fl-stat-lbl { font-size: 11px; color: var(--text-muted); margin-bottom: 8px; }

/* Card Footer */
.fl-card-footer {
    padding: 12px 20px; background: var(--bg-header); display: flex; align-items: center; justify-content: space-between;
}
.fl-airline { display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 800; color: var(--text-badge); }
.fl-airline-logo {
    width: 24px; height: 24px; background: #f59e0b; color: #fff; font-size: 12px; font-weight: 800;
    display: flex; align-items: center; justify-content: center; border-radius: 4px;
}
.fl-actions { display: flex; gap: 10px; }
.fl-btn-action {
    padding: 6px 14px; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; transition: opacity .2s; border: none;
}
.fl-btn-action.simbrief { background: #10b981; color: #fff; }
.fl-btn-action.book { background: var(--text-badge); color: #fff; display: flex; align-items: center; gap: 6px; }
.fl-btn-action:hover { opacity: 0.9; }
.fl-btn-action:disabled { opacity: 0.5; cursor: not-allowed; }

/* Empty state */
.fl-empty { text-align: center; padding: 60px 20px; color: var(--text-muted); background: var(--bg-card); border-radius: 8px; border: 1px solid var(--border-card); }
.fl-empty i { font-size: 48px; color: var(--border-card); margin-bottom: 12px; display: block; }
</style>
@endpush

<div class="fl-wrap">
    <div class="fl-title">
        <i class="ph-fill ph-airplane-tilt"></i> The following flights are ready to book
    </div>

    @if(session('success'))
        <div class="fl-alert success"><i class="ph-fill ph-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="fl-alert error"><i class="ph-fill ph-warning-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="fl-filters">
        <div class="fl-form-group">
            <label>Departure</label>
            <input wire:model.live.debounce="searchDeparture" class="fl-input" placeholder="e.g. EDDL" maxlength="4">
        </div>
        <div class="fl-form-group">
            <label>Arrival</label>
            <input wire:model.live.debounce="searchArrival" class="fl-input" placeholder="e.g. EHAM" maxlength="4">
        </div>
        <div class="fl-form-group">
            <label>Aircraft</label>
            <select wire:model.live="searchType" class="fl-input">
                <option value="">All Types</option>
                @foreach($aircraftOptions as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="fl-form-group">
            <label>Max Duration (hrs)</label>
            <input wire:model.live.debounce="searchDuration" class="fl-input" type="number" step="0.5" placeholder="5">
        </div>
        <div class="fl-btn-group">
            <button wire:click="search" class="fl-btn primary">Search</button>
            <button wire:click="resetSearch" class="fl-btn secondary">Clear</button>
        </div>
    </div>

    @if(count($schedules) > 0)
        <div class="fl-headers">
            <div>Flight Details</div>
            <div>Departure</div>
            <div>Arrival</div>
            <div>Statistics</div>
        </div>

        @foreach($schedules as $schedule)
            <div class="fl-card">
                <div class="fl-card-body">
                    <!-- Column 1 -->
                    <div class="fl-col-details">
                        <div class="fl-flight-num">{{ $schedule->flight_number }}</div>
                        <div class="fl-flight-lbl">Flight Number</div>
                        <div class="fl-aircraft">{{ $schedule->aircraft_type }}</div>
                        <div class="fl-flight-lbl">Aircraft</div>
                    </div>

                    <!-- Column 2 -->
                    <div class="fl-col-route">
                        <div class="fl-airport">
                            <div class="fl-airport-icao"><i class="ph-fill ph-airplane-takeoff"></i> {{ $schedule->departure }}</div>
                            <div class="fl-airport-time">{{ $schedule->departure_time ?? '00:00 UTC' }}</div>
                            <div class="fl-airport-btn">{{ $schedule->departure }} Airport</div>
                        </div>

                        <div class="fl-path">
                            <div class="fl-path-line"></div>
                            <div class="fl-path-plane"><i class="ph-fill ph-airplane-in-flight"></i></div>
                            <div class="fl-path-line"></div>
                        </div>

                        <div class="fl-airport">
                            <div class="fl-airport-icao"><i class="ph-fill ph-airplane-landing"></i> {{ $schedule->arrival }}</div>
                            <div class="fl-airport-time">{{ $schedule->arrival_time ?? '---' }}</div>
                            <div class="fl-airport-btn">{{ $schedule->arrival }} Airport</div>
                        </div>
                    </div>

                    <!-- Column 3 -->
                    <div class="fl-col-stats">
                        <div class="fl-stat-val"><i class="ph-bold ph-arrows-left-right"></i> {{ number_format($schedule->altitude) }}ft</div>
                        <div class="fl-stat-lbl">Altitude</div>
                        <div class="fl-stat-val"><i class="ph-bold ph-clock"></i> {{ $schedule->flight_time }}h</div>
                        <div class="fl-stat-lbl">Flight Time</div>
                    </div>
                </div>

                <div class="fl-card-footer">
                    <div class="fl-airline">
                        <div class="fl-airline-logo">FA</div>
                        FlyAway
                    </div>
                    <div class="fl-actions">
                        @if($schedule->route)
                            <div style="font-size: 11px; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 4px; padding: 5px 10px; color: var(--text-muted); display: flex; align-items: center; font-family: monospace;">
                                ROUTE: {{ $schedule->route }}
                            </div>
                        @endif
                        <button wire:click="book({{ $schedule->id }})" wire:confirm="Book flight {{ $schedule->flight_number }}?" wire:loading.attr="disabled" class="fl-btn-action book">
                            <i wire:loading.remove wire:target="book" class="ph-bold ph-paper-plane-tilt"></i>
                            <span wire:loading wire:target="book">...</span>
                            <span wire:loading.remove wire:target="book">Book Flight</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="fl-empty">
            <i class="ph-fill ph-airplane-tilt"></i>
            <div style="font-size: 15px; font-weight: 700; color: var(--text-primary);">No flights available</div>
            <div style="font-size: 13px; margin-top: 4px;">There are no schedules matching your criteria.</div>
        </div>
    @endif
</div>
