<?php

use App\Models\Bid;
use App\Models\Pirep;
use App\Models\Setting;
use Livewire\Volt\Component;

new class extends Component {
    public $bookings = [];
    public $selectedBidId = '';
    public $flight_number = '';
    public $departure = '';
    public $arrival = '';
    public $aircraft_registration = '';
    public $aircraft_icao = '';
    public $flight_time = '';
    public $landing_rate = '';
    public $route = '';
    public $log = '';

    public function mount(): void
    {
        $this->bookings = Bid::with(['schedule', 'aircraft'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectBooking(): void
    {
        if (!$this->selectedBidId) {
            $this->resetForm();
            return;
        }
        $bid = Bid::with('schedule', 'aircraft')->find($this->selectedBidId);
        if (!$bid) return;

        $this->flight_number = $bid->schedule->flight_number;
        $this->departure = $bid->schedule->departure;
        $this->arrival = $bid->schedule->arrival;
        $this->aircraft_registration = $bid->aircraft->registration;
        $this->aircraft_icao = $bid->aircraft->icao;
        $this->flight_time = $bid->schedule->flight_time;
        $this->route = $bid->schedule->route ?? '';
    }

    public function submit(): void
    {
        $this->validate([
            'flight_number' => 'required|string',
            'departure' => 'required|string|size:4',
            'arrival' => 'required|string|size:4',
            'aircraft_registration' => 'required|string',
            'aircraft_icao' => 'required|string',
            'flight_time' => 'required|numeric|min:0.1|max:30',
            'landing_rate' => 'nullable|numeric|min:-2000|max:2000',
        ]);

        $existing = Pirep::where('user_id', auth()->id())
            ->where('flight_number', $this->flight_number)
            ->whereDate('created_at', today())
            ->exists();

        if ($existing) {
            session()->flash('error', 'You already filed a PIREP for this flight today.');
            return;
        }

        $lr = abs((int) ($this->landing_rate ?: 0));
        $score = match (true) {
            $lr > 500 => 60,
            $lr > 50  => 80,
            default   => 100,
        };

        // Auto-approve if score meets threshold
        $threshold = (int) Setting::get('auto_approve_threshold', 90);
        $status = $score >= $threshold ? 'approved' : 'pending';

        Pirep::create([
            'user_id' => auth()->id(),
            'flight_number' => $this->flight_number,
            'departure' => strtoupper($this->departure),
            'arrival' => strtoupper($this->arrival),
            'aircraft_registration' => $this->aircraft_registration,
            'aircraft_icao' => $this->aircraft_icao,
            'flight_time' => $this->flight_time,
            'landing_rate' => $lr,
            'score' => $score,
            'route' => $this->route,
            'log' => $this->log ?: null,
            'status' => $status,
            'submitted_at' => now(),
        ]);

        // Remove the bid after filing
        if ($this->selectedBidId) {
            Bid::where('id', $this->selectedBidId)->where('user_id', auth()->id())->delete();
        }

        $msg = "PIREP for {$this->flight_number} submitted successfully!";
        if ($status === 'approved') $msg .= ' (Auto-approved)';
        session()->flash('success', $msg);
        $this->resetForm();
        $this->bookings = Bid::with(['schedule', 'aircraft'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function resetForm(): void
    {
        $this->selectedBidId = '';
        $this->flight_number = '';
        $this->departure = '';
        $this->arrival = '';
        $this->aircraft_registration = '';
        $this->aircraft_icao = '';
        $this->flight_time = '';
        $this->landing_rate = '';
        $this->route = '';
        $this->log = '';
    }
}; ?>

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">File a PIREP</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Submit a Pilot Report after completing a flight.</p>
    </div>

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

    <div class="card p-6 space-y-5">
        @if(count($bookings) > 0)
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">From Booking</label>
            <select wire:model.live="selectedBidId" wire:change="selectBooking" class="input-field">
                <option value="">-- Select a booking --</option>
                @foreach($bookings as $b)
                    <option value="{{ $b->id }}">{{ $b->schedule->flight_number }} ({{ $b->schedule->departure }} → {{ $b->schedule->arrival }})</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Number *</label>
                <input wire:model="flight_number" class="input-field" placeholder="FA101">
                @error('flight_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Time (hrs) *</label>
                <input wire:model="flight_time" class="input-field" type="number" step="0.1" placeholder="2.5">
                @error('flight_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Departure *</label>
                <input wire:model="departure" class="input-field" placeholder="YSSY" maxlength="4">
                @error('departure') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Arrival *</label>
                <input wire:model="arrival" class="input-field" placeholder="YMML" maxlength="4">
                @error('arrival') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Aircraft Registration *</label>
                <input wire:model="aircraft_registration" class="input-field" placeholder="VH-NXC">
                @error('aircraft_registration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Aircraft ICAO *</label>
                <input wire:model="aircraft_icao" class="input-field" placeholder="B738">
                @error('aircraft_icao') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Landing Rate (fpm)</label>
                <input wire:model="landing_rate" class="input-field" type="number" placeholder="-150">
                @error('landing_rate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Route</label>
            <input wire:model="route" class="input-field" placeholder="SYD RIC H66 ML">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Log (optional)</label>
            <textarea wire:model="log" class="input-field" rows="3" placeholder="ACARS log or notes about the flight..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button wire:click="resetForm" type="button" class="btn-secondary">Reset</button>
            <button wire:click="submit" wire:loading.attr="disabled" class="btn-primary flex items-center gap-2">
                <svg wire:loading wire:target="submit" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span wire:loading.remove wire:target="submit">Submit PIREP</span>
                <span wire:loading wire:target="submit">Submitting...</span>
            </button>
        </div>
    </div>

    {{-- My PIREPs --}}
    @php
        $myPireps = App\Models\Pirep::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(10)->get();
    @endphp
    @if(count($myPireps) > 0)
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">My Recent PIREPs</h2>
        <div class="space-y-2">
            @foreach($myPireps as $p)
            <div class="card-hover p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $p->flight_number }} &middot; {{ $p->departure }} → {{ $p->arrival }}</p>
                    <p class="text-xs text-slate-500">{{ $p->aircraft_registration }} &middot; {{ $p->flight_time }}h @if($p->landing_rate) &middot; {{ $p->landing_rate }}fpm @endif</p>
                </div>
                <span class="badge-{{ $p->status === 'approved' ? 'success' : ($p->status === 'rejected' ? 'danger' : 'info') }}">
                    {{ ucfirst($p->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
