<?php

use App\Models\Bid;
use App\Services\SimbriefService;
use Livewire\Volt\Component;

new class extends Component {
    public $bookings = [];
    public $selectedBidId = '';
    public $simbriefUsername = '';
    public $ofp = null;
    public $activeTab = 'route';
    public $loading = false;
    public $error = '';

    public function mount(): void
    {
        $this->bookings = Bid::with(['schedule', 'aircraft'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        $this->simbriefUsername = auth()->user()->simbrief_username ?? '';
    }

    public function fetchOFP(): void
    {
        $this->validate([
            'selectedBidId' => 'required',
        ]);

        if (!$this->simbriefUsername) {
            $this->error = 'Please set your SimBrief username in your profile first.';
            return;
        }

        $this->loading = true;
        $this->error = '';
        $this->ofp = null;

        try {
            $service = new SimbriefService();
            $result = $service->fetchOFP($this->simbriefUsername);

            if (!$result) {
                $this->error = 'Could not fetch OFP. Make sure you have an active flight plan in SimBrief.';
                $this->loading = false;
                return;
            }

            $this->ofp = $result;

            // Save OFP data to the bid
            $bid = Bid::find($this->selectedBidId);
            if ($bid) {
                $bid->update(['simbrief_ofp' => json_encode($result)]);
            }
        } catch (\Exception $e) {
            $this->error = 'Error fetching OFP: ' . $e->getMessage();
        }

        $this->loading = false;
    }

    public function setTab($tab): void
    {
        $this->activeTab = $tab;
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">SimBrief Integration</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fetch and view your Operational Flight Plans.</p>
    </div>

    @if($error)
        <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 p-4 text-red-700 dark:text-red-400 text-sm">
            {{ $error }}
        </div>
    @endif

    <div class="card p-5 space-y-4">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Select Booking</label>
                <select wire:model.live="selectedBidId" class="input-field">
                    <option value="">-- Select --</option>
                    @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ optional($b->schedule)->flight_number ?? 'Deleted' }} ({{ optional($b->schedule)->departure ?? '?' }}→{{ optional($b->schedule)->arrival ?? '?' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">SimBrief Username</label>
                <input wire:model="simbriefUsername" class="input-field" placeholder="your_simbrief_username">
            </div>
            <div class="flex items-end">
                <button wire:click="fetchOFP" wire:loading.attr="disabled" class="btn-primary flex items-center gap-2">
                    <svg wire:loading wire:target="fetchOFP" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Fetch OFP
                </button>
            </div>
        </div>
    </div>

    @if($ofp)
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $ofp['flight_number'] }}</h2>
                <p class="text-sm text-slate-500">{{ $ofp['aircraft_icao'] }} &middot; {{ $ofp['departure'] }} → {{ $ofp['arrival'] }}</p>
            </div>
            <div class="text-right text-sm text-slate-500">
                <p>Distance: {{ $ofp['distance'] }}nm</p>
                <p>Flight Time: {{ $ofp['flight_time'] }}</p>
            </div>
        </div>

        <div class="flex gap-1 border-b border-slate-200 dark:border-slate-800 mb-4">
            @foreach(['route' => 'Route', 'weather' => 'Weather', 'fuel' => 'Fuel', 'navlog' => 'NavLog', 'files' => 'Files'] as $key => $label)
                <button wire:click="setTab('{{ $key }}')" class="px-4 py-2 text-sm font-medium transition-all duration-150 border-b-2 -mb-px {{ $activeTab === $key ? 'border-crimson-500 text-crimson-600 dark:text-crimson-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Route Tab --}}
        @if($activeTab === 'route')
        <div>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 font-mono text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                {{ $ofp['route_raw'] }}
            </div>
        </div>
        @endif

        {{-- Weather Tab --}}
        @if($activeTab === 'weather')
        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">{{ $ofp['departure'] }} Departure</p>
                <p class="text-sm font-mono whitespace-pre-wrap">{{ $ofp['weather_dep'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">{{ $ofp['arrival'] }} Arrival</p>
                <p class="text-sm font-mono whitespace-pre-wrap">{{ $ofp['weather_arr'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Alternate</p>
                <p class="text-sm font-mono whitespace-pre-wrap">{{ $ofp['weather_altn'] ?? 'N/A' }}</p>
            </div>
        </div>
        @endif

        {{-- Fuel Tab --}}
        @if($activeTab === 'fuel')
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach([
                'Ramp' => $ofp['fuel_ramp'],
                'Trip' => $ofp['fuel_trip'],
                'Block' => $ofp['fuel_block'],
                'Landing' => $ofp['fuel_plan_landing'],
            ] as $label => $value)
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ $label }}</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $value ?: 'N/A' }}</p>
            </div>
            @endforeach
        </div>
        @endif

        {{-- NavLog Tab --}}
        @if($activeTab === 'navlog')
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <th class="pb-2 px-2">Ident</th>
                        <th class="pb-2 px-2">Name</th>
                        <th class="pb-2 px-2">Altitude</th>
                        <th class="pb-2 px-2">Distance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ofp['waypoints'] as $wp)
                    @php
                        $wpIdent = is_array($wp['ident'] ?? null) ? json_encode($wp['ident']) : ($wp['ident'] ?? '-');
                        $wpName = is_array($wp['name'] ?? null) ? json_encode($wp['name']) : ($wp['name'] ?? '');
                        $wpAlt = is_array($wp['altitude'] ?? null) ? json_encode($wp['altitude']) : ($wp['altitude'] ?? '');
                        $wpDist = is_array($wp['distance'] ?? null) ? json_encode($wp['distance']) : ($wp['distance'] ?? '');
                    @endphp
                    <tr class="border-b border-slate-100 dark:border-slate-800/50">
                        <td class="py-2 px-2 font-mono text-slate-900 dark:text-white">{{ $wpIdent }}</td>
                        <td class="py-2 px-2 text-slate-500">{{ $wpName }}</td>
                        <td class="py-2 px-2 text-slate-500">{{ $wpAlt ? $wpAlt . 'ft' : '-' }}</td>
                        <td class="py-2 px-2 text-slate-500">{{ $wpDist ? $wpDist . 'nm' : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Files Tab --}}
        @if($activeTab === 'files')
        <div class="grid md:grid-cols-2 gap-4">
            @if($ofp['image_url'])
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Route Image</p>
                <img src="{{ $ofp['image_url'] }}" alt="Route Map" class="rounded-xl w-full">
            </div>
            @endif
            @if($ofp['pdf_url'])
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">OFP PDF</p>
                <a href="{{ $ofp['pdf_url'] }}" target="_blank" class="btn-primary inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Download PDF
                </a>
            </div>
            @endif
            @if(!$ofp['image_url'] && !$ofp['pdf_url'])
            <p class="text-slate-500 text-sm">No files available.</p>
            @endif
        </div>
        @endif
    </div>
    @endif
</div>
