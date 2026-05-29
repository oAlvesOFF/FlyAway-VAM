<?php

use App\Models\Aircraft;
use Livewire\Volt\Component;

new class extends Component {
    public $aircraft = [];
    public $showForm = false;
    public $registration = '';
    public $icao = '';
    public $name = '';
    public $category = '';
    public $editingId = null;

    public function mount(): void
    {
        $this->loadAircraft();
    }

    public function loadAircraft(): void
    {
        $this->aircraft = Aircraft::orderBy('registration')->get();
    }

    public function save(): void
    {
        $this->validate([
            'registration' => 'required|string|max:10',
            'icao' => 'required|string|max:4',
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:20',
        ]);

        if ($this->editingId) {
            Aircraft::find($this->editingId)->update([
                'registration' => $this->registration,
                'icao' => $this->icao,
                'name' => $this->name,
                'category' => $this->category,
            ]);
        } else {
            Aircraft::create([
                'registration' => $this->registration,
                'icao' => $this->icao,
                'name' => $this->name,
                'category' => $this->category,
            ]);
        }

        $this->resetForm();
        $this->loadAircraft();
    }

    public function edit($id): void
    {
        $ac = Aircraft::findOrFail($id);
        $this->editingId = $ac->id;
        $this->registration = $ac->registration;
        $this->icao = $ac->icao;
        $this->name = $ac->name;
        $this->category = $ac->category;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Aircraft::findOrFail($id)->delete();
        $this->loadAircraft();
    }

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->registration = '';
        $this->icao = '';
        $this->name = '';
        $this->category = '';
    }
    public function resetMaintenance($id): void
    {
        Aircraft::findOrFail($id)->update([
            'last_service_at' => now(),
            'total_hours_since_service' => 0,
        ]);
        $this->loadAircraft();
        session()->flash('success', 'Maintenance reset.');
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Fleet Manager</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ count($aircraft) }} aircraft registered</p>
        </div>
        <button wire:click="$set('showForm', true)" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Aircraft
        </button>
    </div>

    {{-- Add/Edit Form --}}
    @if($showForm)
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">{{ $editingId ? 'Edit Aircraft' : 'Add Aircraft' }}</h3>
            <form wire:submit="save" class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Registration</label>
                    <input wire:model="registration" class="input-field" placeholder="VH-FLY">
                    @error('registration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ICAO Type</label>
                    <input wire:model="icao" class="input-field" placeholder="B738">
                    @error('icao') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
                    <input wire:model="name" class="input-field" placeholder="Boeing 737-800">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                    <input wire:model="category" class="input-field" placeholder="B737">
                    @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2 flex gap-3">
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                    <button type="button" wire:click="resetForm" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Fleet Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">Registration</th>
                        <th class="p-4 font-medium">ICAO</th>
                        <th class="p-4 font-medium">Name</th>
                        <th class="p-4 font-medium">Category</th>
                        <th class="p-4 font-medium">Location</th>
                        <th class="p-4 font-medium">Status</th>
                        <th class="p-4 font-medium">Maint. Hours</th>
                        <th class="p-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aircraft as $ac)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $ac->registration }}</td>
                            <td class="p-4 text-slate-500">{{ $ac->icao }}</td>
                            <td class="p-4">{{ $ac->name }}</td>
                            <td class="p-4"><span class="badge-info">{{ $ac->category }}</span></td>
                            <td class="p-4 text-slate-500">{{ $ac->location }}</td>
                            <td class="p-4">
                                @if($ac->status === 'active')
                                    <span class="badge-success">Active</span>
                                @elseif($ac->status === 'maintenance')
                                    <span class="badge-warning">Maintenance</span>
                                @else
                                    <span class="badge-danger">In Flight</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="{{ $ac->total_hours_since_service > 100 ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                    {{ number_format($ac->total_hours_since_service, 2) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <button wire:click="resetMaintenance({{ $ac->id }})" wire:confirm="Reset maintenance hours?" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline mr-3">Reset Maint.</button>
                                <button wire:click="edit({{ $ac->id }})" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline mr-3">Edit</button>
                                <button wire:click="delete({{ $ac->id }})" wire:confirm="Delete this aircraft?" class="text-sm text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
