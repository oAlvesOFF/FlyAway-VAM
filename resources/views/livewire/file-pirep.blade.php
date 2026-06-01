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
    public $simbrief_id = '';

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
        $this->simbrief_id = '';

        if (!empty($bid->simbrief_ofp)) {
            $ofp = is_array($bid->simbrief_ofp) ? $bid->simbrief_ofp : json_decode($bid->simbrief_ofp, true);
            if ($ofp) {
                $this->flight_number = $ofp['flight_number'] ?? $this->flight_number;
                $this->departure = $ofp['departure'] ?? $this->departure;
                $this->arrival = $ofp['arrival'] ?? $this->arrival;
                $this->route = $ofp['route_raw'] ?? $this->route;
                $this->aircraft_icao = $ofp['aircraft_icao'] ?? $this->aircraft_icao;
                if (!empty($ofp['aircraft_reg'])) {
                    $this->aircraft_registration = $ofp['aircraft_reg'];
                }
                if (!empty($ofp['simbrief_id'])) {
                    $this->simbrief_id = $ofp['simbrief_id'];
                }
            }
        }
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

        $pirep = Pirep::create([
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

        if ($this->simbrief_id) {
            $simbrief = \App\Models\SimBrief::find($this->simbrief_id);
            if ($simbrief) {
                $simbrief->pirep_id = $pirep->id;
                $simbrief->save();
            }
        }

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
        $this->simbrief_id = '';
    }
}; ?>

<div class="sp-wrap" style="max-width: 800px;">
    <div class="sp-title-area">
        <div class="sp-title">
            <i class="ph-fill ph-paper-plane-tilt"></i> File a PIREP
        </div>
    </div>

    @if(session('success'))
        <div class="sp-card" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2); padding: 16px; margin-bottom: 24px; color: #10b981; font-weight: 600; font-size: 13px;">
            <i class="ph-fill ph-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="sp-card" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); padding: 16px; margin-bottom: 24px; color: #ef4444; font-weight: 600; font-size: 13px;">
            <i class="ph-fill ph-warning-circle" style="margin-right: 6px;"></i> {{ session('error') }}
        </div>
    @endif

    <div class="sp-card" style="margin-bottom: 32px;">
        <div class="sp-card-body" style="display: flex; flex-direction: column; gap: 20px;">
            @if(count($bookings) > 0)
            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">From Booking</div>
                <select wire:model.live="selectedBidId" wire:change="selectBooking" class="sp-input">
                    <option value="">-- Select a booking --</option>
                    @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ $b->schedule->flight_number }} ({{ $b->schedule->departure }} &rarr; {{ $b->schedule->arrival }})</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Flight Number <span style="color: #ef4444;">*</span></div>
                    <input wire:model="flight_number" class="sp-input" placeholder="FA101">
                    @error('flight_number') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Flight Time (hrs) <span style="color: #ef4444;">*</span></div>
                    <input wire:model="flight_time" class="sp-input" type="number" step="0.1" placeholder="2.5">
                    @error('flight_time') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Departure <span style="color: #ef4444;">*</span></div>
                    <input wire:model="departure" class="sp-input" placeholder="YSSY" maxlength="4">
                    @error('departure') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Arrival <span style="color: #ef4444;">*</span></div>
                    <input wire:model="arrival" class="sp-input" placeholder="YMML" maxlength="4">
                    @error('arrival') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Aircraft Registration <span style="color: #ef4444;">*</span></div>
                    <input wire:model="aircraft_registration" class="sp-input" placeholder="VH-NXC">
                    @error('aircraft_registration') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Aircraft ICAO <span style="color: #ef4444;">*</span></div>
                    <input wire:model="aircraft_icao" class="sp-input" placeholder="B738">
                    @error('aircraft_icao') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Landing Rate (fpm)</div>
                    <input wire:model="landing_rate" class="sp-input" type="number" placeholder="-150">
                    @error('landing_rate') <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Route</div>
                <input wire:model="route" class="sp-input" placeholder="SYD RIC H66 ML">
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Flight Log (optional)</div>
                <textarea wire:model="log" class="sp-input" rows="3" placeholder="ACARS log or notes about the flight..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                <button wire:click="resetForm" type="button" class="sp-btn-secondary">Reset</button>
                <button wire:click="submit" wire:loading.attr="disabled" class="sp-btn-primary">
                    <i wire:loading.remove wire:target="submit" class="ph-bold ph-paper-plane-tilt"></i>
                    <span wire:loading wire:target="submit">...</span>
                    <span wire:loading.remove wire:target="submit">Submit PIREP</span>
                </button>
            </div>
        </div>
    </div>

    {{-- My PIREPs --}}
    @php
        $myPireps = App\Models\Pirep::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(10)->get();
    @endphp
    @if(count($myPireps) > 0)
    <div>
        <div style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">
            <i class="ph-fill ph-clock-counter-clockwise"></i> My Recent PIREPs
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($myPireps as $p)
            <div class="sp-card card-hover" style="padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">{{ $p->flight_number }} &middot; {{ $p->departure }} &rarr; {{ $p->arrival }}</div>
                    <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500;">
                        {{ $p->aircraft_registration }} &middot; {{ $p->flight_time }}h 
                        @if($p->landing_rate) &middot; {{ $p->landing_rate }}fpm @endif
                    </div>
                </div>
                <span class="sp-badge {{ $p->status === 'approved' ? 'approved' : ($p->status === 'rejected' ? 'rejected' : 'pending') }}">
                    {{ ucfirst($p->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
