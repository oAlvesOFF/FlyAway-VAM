<?php

use App\Models\Achievement;
use Livewire\Volt\Component;

new class extends Component {
    public $filter = 'all';

    public function checkNow(): void
    {
        $new = Achievement::checkAndUnlock(auth()->user());
        if (count($new) > 0) {
            $names = collect($new)->pluck('name')->implode(', ');
            session()->flash('achievement', "Achievements unlocked: {$names}!");
        } else {
            session()->flash('info', 'No new achievements to unlock.');
        }
    }

    public function with(): array
    {
        $user = auth()->user();
        $query = Achievement::with(['users' => fn($q) => $q->where('user_id', $user->id)]);

        if ($this->filter !== 'all') {
            $query->where('category', $this->filter);
        }

        $all = $query->orderBy('category')->orderBy('threshold')->get();
        $unlocked = $all->filter(fn($a) => $a->users->isNotEmpty());
        $locked = $all->filter(fn($a) => $a->users->isEmpty());

        return [
            'unlocked' => $unlocked,
            'locked' => $locked,
            'categories' => Achievement::select('category')->distinct()->pluck('category'),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Achievements</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track your progress and unlock rewards.</p>
        </div>
        <button wire:click="checkNow" class="btn-primary text-sm px-4 py-2">Check Progress</button>
    </div>

    @if(session('achievement'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">{{ session('achievement') }}</div>
    @endif
    @if(session('info'))
        <div class="card bg-sky-50 dark:bg-sky-900/20 border-sky-200 dark:border-sky-800 p-4 text-sky-700 dark:text-sky-400 text-sm">{{ session('info') }}</div>
    @endif

    <div class="flex gap-2 flex-wrap">
        <button wire:click="$set('filter', 'all')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 {{ $filter === 'all' ? 'bg-crimson-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">All</button>
        @foreach($categories as $cat)
            <button wire:click="$set('filter', '{{ $cat }}')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 {{ $filter === $cat ? 'bg-crimson-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ ucfirst($cat) }}</button>
        @endforeach
    </div>

    @if($unlocked->isNotEmpty())
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Unlocked ({{ $unlocked->count() }})</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($unlocked as $a)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                        <span class="text-2xl">{{ $a->icon }}</span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $a->name }}</p>
                            <p class="text-xs text-slate-500">{{ $a->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($locked->isNotEmpty())
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Locked ({{ $locked->count() }})</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($locked as $a)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 opacity-60">
                        <span class="text-2xl">{{ $a->icon }}</span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $a->name }}</p>
                            <p class="text-xs text-slate-500">{{ $a->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
