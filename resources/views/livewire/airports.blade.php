<?php

use App\Models\Airport;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with(): array
    {
        $query = Airport::query();
        if ($this->search) {
            $query->where('icao', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
        }

        return [
            'airports' => $query->orderBy('icao')->paginate(20),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Airport Database</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lookup airport details and coordinates.</p>
    </div>

    <div class="card p-4">
        <input wire:model.live.debounce.300ms="search" type="text" class="input-field w-full" placeholder="Search by ICAO, Name, or City...">
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">ICAO</th>
                        <th class="p-4 font-medium">Name</th>
                        <th class="p-4 font-medium">City</th>
                        <th class="p-4 font-medium">Country</th>
                        <th class="p-4 font-medium">Coordinates</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($airports as $ap)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-mono font-medium text-crimson-600 dark:text-crimson-400">{{ $ap->icao }}</td>
                        <td class="p-4 text-slate-900 dark:text-white">{{ $ap->name }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">{{ $ap->city }}</td>
                        <td class="p-4 text-slate-500">{{ $ap->country }}</td>
                        <td class="p-4 text-slate-500 font-mono text-xs">{{ $ap->lat }}, {{ $ap->lng }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $airports->links() }}
        </div>
    </div>
</div>
