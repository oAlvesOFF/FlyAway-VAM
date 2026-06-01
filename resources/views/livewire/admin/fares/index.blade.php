<?php

use App\Models\Fare;
use Livewire\Volt\Component;

new class extends Component {
    public $fares = [];
    public $showForm = false;
    public $editingId = null;
    
    public $code = '';
    public $name = '';
    public $type = 1;
    public $price = 0;
    public $cost = 0;
    public $capacity = 0;
    public $active = true;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->fares = Fare::orderBy('name')->get()->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $fare = Fare::find($id);
        if (!$fare) return;
        $this->editingId = $fare->id;
        $this->code = $fare->code;
        $this->name = $fare->name;
        $this->type = $fare->type;
        $this->price = $fare->price;
        $this->cost = $fare->cost;
        $this->capacity = $fare->capacity;
        $this->active = $fare->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|integer',
        ]);

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'price' => $this->price,
            'cost' => $this->cost,
            'capacity' => $this->capacity,
            'active' => $this->active,
        ];

        if ($this->editingId) {
            Fare::find($this->editingId)->update($data);
        } else {
            Fare::create($data);
        }

        $this->showForm = false;
        $this->loadData();
    }

    public function delete($id): void
    {
        Fare::find($id)?->delete();
        $this->loadData();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->type = 1;
        $this->price = 0;
        $this->cost = 0;
        $this->capacity = 0;
        $this->active = true;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Fares</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage seating/cargo classes and pricing.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Fare</button>
    </div>

    @if($showForm)
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId ? 'Edit Fare' : 'New Fare' }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label>
                    <input wire:model="name" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" placeholder="e.g. Economy Class">
                    @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Code *</label>
                    <input wire:model="code" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" placeholder="e.g. Y">
                    @error('code') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Type</label>
                    <select wire:model="type" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                        <option value="1">Passenger</option>
                        <option value="2">Cargo</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Ticket Price</label>
                    <input type="number" step="0.01" wire:model="price" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
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
        @forelse($fares as $fare)
            <div class="card-hover p-4 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ $fare['name'] }} ({{ $fare['code'] }})</h3>
                        <span class="badge-info text-xs">{{ $fare['type'] == 1 ? 'Passenger' : 'Cargo' }}</span>
                        @if(!$fare['active']) <span class="badge-error text-xs">Inactive</span> @endif
                    </div>
                    <p class="text-xs text-slate-500">Price: ${{ $fare['price'] }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $fare['id'] }})" class="text-xs text-crimson-600 hover:underline">Edit</button>
                    <button wire:click="delete({{ $fare['id'] }})" wire:confirm="Delete this fare?" class="text-xs text-red-500 hover:underline">Delete</button>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <p>No fares found.</p>
            </div>
        @endforelse
    </div>
</div>
