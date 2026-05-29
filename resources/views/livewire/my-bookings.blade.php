<?php

use App\Models\Bid;
use Livewire\Volt\Component;

new class extends Component {
    public $bookings = [];

    public function mount(): void
    {
        $this->loadBookings();
    }

    public function loadBookings(): void
    {
        $this->bookings = Bid::with(['schedule', 'aircraft'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function cancel($bidId): void
    {
        Bid::where('id', $bidId)->where('user_id', auth()->id())->delete();
        session()->flash('success', 'Booking cancelled successfully.');
        $this->loadBookings();
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Bookings</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your current flight bookings.</p>
    </div>

    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @forelse($bookings as $bid)
        @php $sched = $bid->schedule; $ac = $bid->aircraft; @endphp
        <div class="card-hover p-5 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="text-center min-w-[70px]">
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ optional($sched)->departure ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500">{{ optional($sched)->departure_time ?? '--' }}</p>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <span class="text-xs font-medium text-slate-400">{{ optional($sched)->flight_number ?? 'Deleted' }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-px bg-slate-300 dark:bg-slate-600"></div>
                        <svg class="w-4 h-4 text-crimson-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                        <div class="w-8 h-px bg-slate-300 dark:bg-slate-600"></div>
                    </div>
                    <span class="text-xs text-slate-400">{{ optional($sched)->flight_time ?? '-' }}h</span>
                </div>
                <div class="text-center min-w-[70px]">
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ optional($sched)->arrival ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500">---</p>
                </div>
                <div class="text-sm text-slate-500">
                    <span class="badge-info">{{ optional($ac)->registration ?? 'N/A' }}</span>
                    <p class="text-xs mt-1">Aircraft: {{ optional($ac)->icao ?? '-' }}</p>
                </div>
            </div>
            <button wire:click="cancel({{ $bid->id }})" wire:confirm="Cancel this booking?" wire:loading.attr="disabled" class="btn-secondary text-red-600 dark:text-red-400 border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                <svg wire:loading wire:target="cancel" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span wire:loading.remove wire:target="cancel">Cancel</span>
                <span wire:loading wire:target="cancel">Cancelling...</span>
            </button>
        </div>
    @empty
        <div class="card p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <p class="text-slate-500 dark:text-slate-400">You have no active bookings.</p>
            <a href="{{ route('flights') }}" wire:navigate class="btn-primary inline-flex mt-4">Browse Flights</a>
        </div>
    @endforelse
</div>
