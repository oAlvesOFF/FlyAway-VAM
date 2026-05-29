<?php

use App\Models\Schedule;
use Livewire\Volt\Component;

new class extends Component {
    public $schedules = [];
    public $showForm = false;
    public $flight_number = '';
    public $departure = '';
    public $arrival = '';
    public $route = '';
    public $aircraft_type = '';
    public $flight_time = 0;
    public $departure_time = '12:00';
    public $altitude = 30000;
    public $editingId = null;

    public function mount(): void
    {
        $this->loadSchedules();
    }

    public function loadSchedules(): void
    {
        $this->schedules = Schedule::orderBy('flight_number')->get();
    }

    public function save(): void
    {
        $this->validate([
            'flight_number' => 'required|string|max:10',
            'departure' => 'required|string|size:4',
            'arrival' => 'required|string|size:4',
            'route' => 'required|string',
            'aircraft_type' => 'required|string|max:20',
            'flight_time' => 'required|numeric|min:0.1',
            'departure_time' => 'required|string',
            'altitude' => 'required|integer|min:0',
        ]);

        $data = [
            'flight_number' => $this->flight_number,
            'departure' => strtoupper($this->departure),
            'arrival' => strtoupper($this->arrival),
            'route' => $this->route,
            'aircraft_type' => $this->aircraft_type,
            'flight_time' => $this->flight_time,
            'departure_time' => $this->departure_time,
            'altitude' => $this->altitude,
        ];

        if ($this->editingId) {
            Schedule::find($this->editingId)->update($data);
        } else {
            Schedule::create($data);
        }

        $this->resetForm();
        $this->loadSchedules();
    }

    public function edit($id): void
    {
        $s = Schedule::findOrFail($id);
        $this->editingId = $s->id;
        $this->flight_number = $s->flight_number;
        $this->departure = $s->departure;
        $this->arrival = $s->arrival;
        $this->route = $s->route;
        $this->aircraft_type = $s->aircraft_type;
        $this->flight_time = $s->flight_time;
        $this->departure_time = $s->departure_time;
        $this->altitude = $s->altitude;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Schedule::findOrFail($id)->delete();
        $this->loadSchedules();
    }

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->flight_number = '';
        $this->departure = '';
        $this->arrival = '';
        $this->route = '';
        $this->aircraft_type = '';
        $this->flight_time = 0;
        $this->departure_time = '12:00';
        $this->altitude = 30000;
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Schedules</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ count($schedules) }} flights scheduled</p>
        </div>
        <button wire:click="$set('showForm', true)" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Schedule
        </button>
    </div>

    @if($showForm)
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">{{ $editingId ? 'Edit Schedule' : 'Add Schedule' }}</h3>
            <form wire:submit="save" class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Number</label>
                    <input wire:model="flight_number" class="input-field" placeholder="VA001">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Departure (ICAO)</label>
                    <input wire:model="departure" class="input-field" placeholder="YSSY" maxlength="4" style="text-transform: uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Arrival (ICAO)</label>
                    <input wire:model="arrival" class="input-field" placeholder="YMML" maxlength="4" style="text-transform: uppercase">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Route</label>
                    <input wire:model="route" class="input-field" placeholder="YSSY DCT WOL DCT YMML">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Aircraft Type</label>
                    <input wire:model="aircraft_type" class="input-field" placeholder="B738">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Time (hrs)</label>
                    <input wire:model="flight_time" class="input-field" type="number" step="0.1" placeholder="2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Altitude</label>
                    <input wire:model="altitude" class="input-field" type="number" placeholder="30000">
                </div>
                <div class="md:col-span-3 flex gap-3">
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                    <button type="button" wire:click="resetForm" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">Flight</th>
                        <th class="p-4 font-medium">Route</th>
                        <th class="p-4 font-medium">Type</th>
                        <th class="p-4 font-medium">Time</th>
                        <th class="p-4 font-medium">Departure</th>
                        <th class="p-4 font-medium">Altitude</th>
                        <th class="p-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $s)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $s->flight_number }}</td>
                            <td class="p-4">{{ $s->departure }} → {{ $s->arrival }}</td>
                            <td class="p-4"><span class="badge-info">{{ $s->aircraft_type }}</span></td>
                            <td class="p-4">{{ $s->flight_time }}h</td>
                            <td class="p-4 text-slate-500">{{ $s->departure_time }}</td>
                            <td class="p-4 text-slate-500">{{ number_format($s->altitude) }}ft</td>
                            <td class="p-4 text-right">
                                <button wire:click="edit({{ $s->id }})" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline mr-3">Edit</button>
                                <button wire:click="delete({{ $s->id }})" wire:confirm="Delete this schedule?" class="text-sm text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
