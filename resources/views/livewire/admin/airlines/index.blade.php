<?php

use App\Models\Airline;
use Livewire\Volt\Component;

new class extends Component {
    public $airlines = [];
    public $showForm = false;
    public $editingId = null;
    
    public $icao = '';
    public $iata = '';
    public $name = '';
    public $callsign = '';
    public $country = '';
    public $active = true;

    public function mount(): void
    {
        $this->loadAirlines();
    }

    public function loadAirlines(): void
    {
        $this->airlines = Airline::orderBy('name')->get()->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $airline = Airline::find($id);
        if (!$airline) return;
        $this->editingId = $airline->id;
        $this->icao = $airline->icao;
        $this->iata = $airline->iata;
        $this->name = $airline->name;
        $this->callsign = $airline->callsign;
        $this->country = $airline->country;
        $this->active = $airline->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'icao' => 'required|string|max:5',
            'name' => 'required|string|max:255',
        ]);

        $data = [
            'icao' => $this->icao,
            'iata' => $this->iata,
            'name' => $this->name,
            'callsign' => $this->callsign,
            'country' => $this->country,
            'active' => $this->active,
        ];

        if ($this->editingId) {
            Airline::find($this->editingId)->update($data);
        } else {
            Airline::create($data);
        }

        $this->showForm = false;
        $this->loadAirlines();
    }

    public function delete($id): void
    {
        Airline::find($id)?->delete();
        $this->loadAirlines();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->icao = '';
        $this->iata = '';
        $this->name = '';
        $this->callsign = '';
        $this->country = '';
        $this->active = true;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Airlines</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage airlines within the system.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Airline</button>
    </div>

    @if($showForm)
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId ? 'Edit Airline' : 'New Airline' }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label>
                    <input wire:model="name" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">ICAO *</label>
                    <input wire:model="icao" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm uppercase">
                    @error('icao') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">IATA</label>
                    <input wire:model="iata" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm uppercase">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Callsign</label>
                    <input wire:model="callsign" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Country</label>
                    <input wire:model="country" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
                <div class="space-y-1 flex items-end">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="active" class="rounded border-slate-300 dark:border-slate-600">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="save" class="btn-primary text-sm px-4 py-2">Save</button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">Cancel</button>
            </div>
        </div>
    @endif

    <div class="space-y-2">
        @forelse($airlines as $airline)
            <div class="card-hover p-4 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ $airline['name'] }} ({{ $airline['icao'] }})</h3>
                        @if($airline['active'])
                            <span class="badge-success text-xs">Active</span>
                        @else
                            <span class="badge-error text-xs">Inactive</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">Callsign: {{ $airline['callsign'] ?: 'N/A' }} &middot; Country: {{ $airline['country'] ?: 'N/A' }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $airline['id'] }})" class="text-xs text-crimson-600 hover:underline">Edit</button>
                    <button wire:click="delete({{ $airline['id'] }})" wire:confirm="Delete this airline?" class="text-xs text-red-500 hover:underline">Delete</button>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <p>No airlines found.</p>
            </div>
        @endforelse
    </div>
</div>
