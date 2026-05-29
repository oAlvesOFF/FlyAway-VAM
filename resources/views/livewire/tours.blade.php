<?php

use App\Models\Tour;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        $tours = Tour::where('is_active', true)->with(['users' => fn($q) => $q->where('user_id', $user->id)])->orderBy('order')->get();

        return [
            'tours' => $tours,
            'userFlights' => $user->pireps()->select('departure', 'arrival')->get(),
        ];
    }

    public function calculateProgress($waypoints, $userFlights): int
    {
        if (empty($waypoints) || count($waypoints) < 2) return 0;
        $segments = count($waypoints) - 1;
        $completed = 0;
        for ($i = 0; $i < $segments; $i++) {
            $dep = $waypoints[$i];
            $arr = $waypoints[$i + 1];
            if ($userFlights->contains(fn($f) => $f->departure === $dep && $f->arrival === $arr)) {
                $completed++;
            }
        }
        return $segments > 0 ? round(($completed / $segments) * 100) : 0;
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tours</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Complete themed route tours to earn special recognition.</p>
    </div>

    @if($tours->isEmpty())
        <div class="card p-6 text-center text-slate-400">No tours available yet.</div>
    @else
        <div class="grid gap-4">
            @foreach($tours as $tour)
                @php
                    $pivot = $tour->users->first()?->pivot;
                    $completed = $pivot?->completed ?? false;
                    $progress = $completed ? 100 : $this->calculateProgress($tour->waypoints, $userFlights);
                @endphp
                <div class="card p-5 {{ $completed ? 'border-emerald-300 dark:border-emerald-700' : '' }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $tour->name }}</h3>
                            <p class="text-sm text-slate-500">{{ $tour->description }}</p>
                        </div>
                        @if($completed)
                            <span class="badge-success">Completed</span>
                        @endif
                    </div>

                    @if($tour->waypoints)
                        <div class="flex items-center gap-2 flex-wrap mb-3">
                            @foreach($tour->waypoints as $i => $wpt)
                                <span class="text-sm font-mono font-bold {{ $i === 0 ? 'text-crimson-600' : '' }}">{{ $wpt }}</span>
                                @if($i < count($tour->waypoints) - 1)
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $completed ? 'bg-emerald-500' : 'bg-crimson-500' }}" style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ $progress }}% complete</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
