<?php

use App\Models\Typerating;
use Livewire\Volt\Component;

new class extends Component {
    public $typeratings = [];
    public $showForm = false;
    public $editingId = null;
    
    public $name = '';
    public $type = '';
    public $description = '';
    public $active = true;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->typeratings = Typerating::orderBy('name')->get()->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $typerating = Typerating::find($id);
        if (!$typerating) return;
        $this->editingId = $typerating->id;
        $this->name = $typerating->name;
        $this->type = $typerating->type;
        $this->description = $typerating->description;
        $this->active = $typerating->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'active' => $this->active,
        ];

        if ($this->editingId) {
            Typerating::find($this->editingId)->update($data);
        } else {
            Typerating::create($data);
        }

        $this->showForm = false;
        $this->loadData();
    }

    public function delete($id): void
    {
        Typerating::find($id)?->delete();
        $this->loadData();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->type = '';
        $this->description = '';
        $this->active = true;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Type Ratings</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage pilot certifications.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Type Rating</button>
    </div>

    @if($showForm)
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId ? 'Edit Type Rating' : 'New Type Rating' }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label>
                    <input wire:model="name" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" placeholder="e.g. A320 Family">
                    @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Type Code *</label>
                    <input wire:model="type" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" placeholder="e.g. A320">
                    @error('type') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                    <textarea wire:model="description" rows="2" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm"></textarea>
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
        @forelse($typeratings as $tr)
            <div class="card-hover p-4 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ $tr['name'] }}</h3>
                        @if($tr['active'])
                            <span class="badge-success text-xs">Active</span>
                        @else
                            <span class="badge-error text-xs">Inactive</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">Type Code: {{ $tr['type'] }} &middot; {{ $tr['description'] }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $tr['id'] }})" class="text-xs text-crimson-600 hover:underline">Edit</button>
                    <button wire:click="delete({{ $tr['id'] }})" wire:confirm="Delete this type rating?" class="text-xs text-red-500 hover:underline">Delete</button>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <p>No type ratings found.</p>
            </div>
        @endforelse
    </div>
</div>
