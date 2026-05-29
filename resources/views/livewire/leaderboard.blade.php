<?php

use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    public string $sort = 'hours';
    public $pilots = [];

    public function mount(): void
    {
        $this->loadPilots();
    }

    public function updatedSort(): void
    {
        $this->loadPilots();
    }

    public function loadPilots(): void
    {
        $query = User::with('rank')->where('is_admin', false)->where('status', 'active');

        if ($this->sort === 'hours') {
            $query->orderBy('total_hours', 'desc');
        } elseif ($this->sort === 'flights') {
            $query->orderBy('total_flights', 'desc');
        } elseif ($this->sort === 'avg_score') {
            $query->withAvg('pireps', 'score')->orderBy('pireps_avg_score', 'desc');
        }

        $this->pilots = $query->take(50)->get()->toArray();
    }
}; ?>

<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pilot Leaderboard</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Top pilots ranked by hours, flights, and scores.</p>
    </div>

    <div class="flex gap-2">
        @foreach(['hours' => 'Total Hours', 'flights' => 'Flights Logged', 'avg_score' => 'Avg Score'] as $key => $label)
            <button wire:click="$set('sort', '{{ $key }}')"
                class="px-4 py-1.5 rounded-xl text-sm font-medium transition-all duration-150 {{ $sort === $key ? 'bg-crimson-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if(count($pilots) > 0)
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <th class="p-4 font-medium w-12">#</th>
                            <th class="p-4 font-medium">Pilot</th>
                            <th class="p-4 font-medium">ID</th>
                            <th class="p-4 font-medium">Rank</th>
                            <th class="p-4 font-medium text-right">Hours</th>
                            <th class="p-4 font-medium text-right">Flights</th>
                            <th class="p-4 font-medium text-right">Avg Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pilots as $idx => $p)
                            <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-4">
                                    @if($idx === 0)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-yellow-400 text-yellow-900 text-xs font-bold">1</span>
                                    @elseif($idx === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 dark:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold">2</span>
                                    @elseif($idx === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-white text-xs font-bold">3</span>
                                    @else
                                        <span class="text-slate-400 text-xs font-bold pl-2">{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $p['name'] }}</span>
                                </td>
                                <td class="p-4 text-slate-500">{{ $p['pilot_id'] }}</td>
                                <td class="p-4">
                                    <span class="badge-info">{{ $p['rank']['name'] ?? 'Candidate' }}</span>
                                </td>
                                <td class="p-4 text-right font-mono text-slate-900 dark:text-white">{{ number_format($p['total_hours'], 1) }}</td>
                                <td class="p-4 text-right font-mono text-slate-900 dark:text-white">{{ $p['total_flights'] }}</td>
                                <td class="p-4 text-right font-mono text-slate-900 dark:text-white">{{ isset($p['pireps_avg_score']) ? number_format($p['pireps_avg_score'], 1) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card p-8 text-center text-slate-400">
            <p>No pilots found.</p>
        </div>
    @endif
</div>
