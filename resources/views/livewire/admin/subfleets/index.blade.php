<?php

use App\Models\Subfleet;
use App\Models\Airline;
use Livewire\Volt\Component;

new class extends Component {
    public $subfleets = [];
    public $airlines = [];
    public $showForm = false;
    public $editingId = null;
    
    public $airline_id = '';
    public $type = '';
    public $simbrief_type = '';
    public $name = '';
    public $cost_block_hour = '';
    public $cost_delay_minute = '';
    public $ground_handling_multiplier = '';
    public $cargo_capacity = '';
    public $fuel_capacity = '';
    public $gross_weight = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->subfleets = Subfleet::with('airline')->orderBy('name')->get()->toArray();
        $this->airlines = Airline::where('active', true)->orderBy('name')->get()->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $subfleet = Subfleet::find($id);
        if (!$subfleet) return;
        $this->editingId = $subfleet->id;
        $this->airline_id = $subfleet->airline_id;
        $this->type = $subfleet->type;
        $this->simbrief_type = $subfleet->simbrief_type;
        $this->name = $subfleet->name;
        $this->cost_block_hour = $subfleet->cost_block_hour;
        $this->cost_delay_minute = $subfleet->cost_delay_minute;
        $this->ground_handling_multiplier = $subfleet->ground_handling_multiplier;
        $this->cargo_capacity = $subfleet->cargo_capacity;
        $this->fuel_capacity = $subfleet->fuel_capacity;
        $this->gross_weight = $subfleet->gross_weight;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'airline_id' => 'required|exists:airlines,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $data = [
            'airline_id' => $this->airline_id ?: null,
            'type' => $this->type,
            'simbrief_type' => $this->simbrief_type,
            'name' => $this->name,
            'cost_block_hour' => $this->cost_block_hour ?: null,
            'cost_delay_minute' => $this->cost_delay_minute ?: null,
            'ground_handling_multiplier' => $this->ground_handling_multiplier ?: null,
            'cargo_capacity' => $this->cargo_capacity ?: null,
            'fuel_capacity' => $this->fuel_capacity ?: null,
            'gross_weight' => $this->gross_weight ?: null,
        ];

        if ($this->editingId) {
            Subfleet::find($this->editingId)->update($data);
        } else {
            Subfleet::create($data);
        }

        $this->showForm = false;
        $this->loadData();
    }

    public function delete($id): void
    {
        Subfleet::find($id)?->delete();
        $this->loadData();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->airline_id = '';
        $this->type = '';
        $this->simbrief_type = '';
        $this->name = '';
        $this->cost_block_hour = '';
        $this->cost_delay_minute = '';
        $this->ground_handling_multiplier = '';
        $this->cargo_capacity = '';
        $this->fuel_capacity = '';
        $this->gross_weight = '';
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Subfleets</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage aircraft subfleets and economic data.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Subfleet</button>
    </div>

    @if($showForm)
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId ? 'Edit Subfleet' : 'New Subfleet' }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label>
                    <input wire:model="name" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" placeholder="e.g. A320-200 CFM">
                    @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Airline *</label>
                    <select wire:model="airline_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                        <option value="">Select Airline...</option>
                        @foreach($airlines as $airline)
                            <option value="{{ $airline['id'] }}">{{ $airline['name'] }} ({{ $airline['icao'] }})</option>
                        @endforeach
                    </select>
                    @error('airline_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Type *</label>
                    <input wire:model="type" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" placeholder="e.g. A320">
                    @error('type') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">SimBrief Type</label>
                    <input wire:model="simbrief_type" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Cost per Block Hour</label>
                    <input type="number" step="0.01" wire:model="cost_block_hour" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Cargo Capacity</label>
                    <input type="number" step="0.01" wire:model="cargo_capacity" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="save" class="btn-primary text-sm px-4 py-2">Save</button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">Cancel</button>
            </div>
        </div>
    @endif

    <div class="space-y-2">
        @forelse($subfleets as $subfleet)
            <div class="card-hover p-4 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $subfleet['name'] }} ({{ $subfleet['type'] }})</h3>
                    <p class="text-xs text-slate-500">Airline: {{ $subfleet['airline']['name'] ?? 'N/A' }} &middot; Cost/Hr: ${{ $subfleet['cost_block_hour'] ?? '0.00' }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $subfleet['id'] }})" class="text-xs text-crimson-600 hover:underline">Edit</button>
                    <button wire:click="delete({{ $subfleet['id'] }})" wire:confirm="Delete this subfleet?" class="text-xs text-red-500 hover:underline">Delete</button>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <p>No subfleets found.</p>
            </div>
        @endforelse
    </div>
</div>
