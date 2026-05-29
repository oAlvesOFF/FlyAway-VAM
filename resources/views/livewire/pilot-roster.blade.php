<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = User::with('rank')
            ->where('status', 'active')
            ->orderBy('total_hours', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('pilot_id', 'like', '%' . $this->search . '%');
            });
        }

        return ['pilots' => $query->paginate(20)];
    }
}; ?>

<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pilot Roster</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Meet our active pilots at Atlantic Star Airways.</p>
    </div>

    <div class="flex items-center gap-3">
        <input wire:model.live.debounce="search" placeholder="Search by name or pilot ID..." class="input-field text-sm px-4 py-2 w-full max-w-sm">
        <span class="text-xs text-slate-400">{{ $pilots->total() }} pilots</span>
    </div>

    <div class="grid gap-3">
        @forelse($pilots as $pilot)
            <div class="card-hover p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-crimson-100 dark:bg-crimson-900/30 flex items-center justify-center text-lg font-bold text-crimson-600 dark:text-crimson-400 shrink-0 overflow-hidden">
                    @if($pilot->avatar)
                        <img src="{{ Storage::url($pilot->avatar) }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ substr($pilot->name, 0, 1) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('pilot-profile', $pilot->id) }}" class="font-semibold text-slate-900 dark:text-white truncate hover:underline hover:text-crimson-600 dark:hover:text-crimson-400 transition-colors">
                        {{ $pilot->name }}
                    </a>
                    <p class="text-xs text-slate-500">{{ $pilot->pilot_id }} &middot; {{ $pilot->rank?->name ?? 'Recruit' }}</p>
                </div>
                <div class="hidden sm:flex items-center gap-6 text-sm">
                    <div class="text-center">
                        <p class="font-bold text-slate-900 dark:text-white">{{ number_format($pilot->total_hours, 1) }}</p>
                        <p class="text-xs text-slate-400">Hours</p>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-slate-900 dark:text-white">{{ $pilot->total_flights }}</p>
                        <p class="text-xs text-slate-400">Flights</p>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-slate-900 dark:text-white">{{ $pilot->last_location }}</p>
                        <p class="text-xs text-slate-400">Location</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
                <p>No active pilots found</p>
            </div>
        @endforelse
    </div>

    @if($pilots->hasPages())
        <div>{{ $pilots->links() }}</div>
    @endif
</div>
