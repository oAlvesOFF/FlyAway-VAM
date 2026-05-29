<?php

use App\Models\Pirep;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    use WithPagination;

    public string $filter = 'all';
    public $selectedPirepId = null;
    public $editingPirepId = null;
    public $editFlightTime = '';
    public $editLandingRate = '';
    public $editRoute = '';
    public $editLog = '';

    public function with(): array
    {
        $query = Pirep::where('user_id', auth()->id());

        if ($this->filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($this->filter === 'approved') {
            $query->where('status', 'approved');
        } elseif ($this->filter === 'rejected') {
            $query->where('status', 'rejected');
        }

        return [
            'pireps' => $query->orderBy('created_at', 'desc')->paginate(10),
        ];
    }

    public function toggleDetail($id): void
    {
        $this->selectedPirepId = $this->selectedPirepId === $id ? null : $id;
        $this->editingPirepId = null;
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
        $this->selectedPirepId = null;
        $this->editingPirepId = null;
    }

    public function editPirep($id): void
    {
        $pirep = Pirep::where('user_id', auth()->id())->where('status', 'pending')->find($id);
        if (!$pirep) return;

        $this->editingPirepId = $id;
        $this->selectedPirepId = $id;
        $this->editFlightTime = (string) $pirep->flight_time;
        $this->editLandingRate = (string) ($pirep->landing_rate ?? '');
        $this->editRoute = $pirep->route ?? '';
        $this->editLog = $pirep->log ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editingPirepId = null;
    }

    public function updatePirep(): void
    {
        $pirep = Pirep::where('user_id', auth()->id())->where('status', 'pending')->find($this->editingPirepId);
        if (!$pirep) return;

        $this->validate([
            'editFlightTime' => 'required|numeric|min:0.1|max:30',
            'editLandingRate' => 'nullable|numeric|min:-2000|max:2000',
            'editRoute' => 'nullable|string',
            'editLog' => 'nullable|string',
        ]);

        $lr = abs((int) ($this->editLandingRate ?? 0));
        $score = match (true) {
            $lr > 500 => 60,
            $lr > 50  => 80,
            default   => 100,
        };

        $pirep->update([
            'flight_time' => $this->editFlightTime,
            'landing_rate' => $this->editLandingRate ?: null,
            'route' => $this->editRoute ?: null,
            'log' => $this->editLog ?: null,
            'score' => $score,
        ]);

        $this->editingPirepId = null;
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My PIREPs</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">All your Pilot Reports in one place.</p>
        </div>
        <a href="{{ route('file-pirep') }}" wire:navigate class="btn-primary text-sm px-4 py-2">File New PIREP</a>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')"
                class="px-4 py-1.5 rounded-xl text-sm font-medium transition-all duration-150 {{ $filter === $key ? 'bg-crimson-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- PIREP List --}}
    <div class="space-y-2">
        @forelse($pireps as $p)
            <div wire:key="pirep-{{ $p->id }}">
                <div class="card-hover p-4 flex items-center justify-between cursor-pointer" wire:click="toggleDetail({{ $p->id }})">
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $p->flight_number }}</p>
                            <p class="text-xs text-slate-500">{{ $p->departure }} &rarr; {{ $p->arrival }}</p>
                        </div>
                        <div class="hidden sm:block text-xs text-slate-400">
                            <p>{{ $p->aircraft_registration }} ({{ $p->aircraft_icao }})</p>
                            <p>{{ number_format($p->flight_time, 2) }}h @if($p->landing_rate !== null) &middot; {{ $p->landing_rate }}fpm @endif</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400 hidden sm:inline">{{ $p->submitted_at ? (\Carbon\Carbon::parse($p->submitted_at)->format('d M Y')) : (\Carbon\Carbon::parse($p->created_at)->format('d M Y')) }}</span>
                        <span class="badge-{{ $p->status === 'approved' ? 'success' : ($p->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($p->status) }}
                        </span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ $selectedPirepId === $p->id ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </div>
                @if($selectedPirepId === $p->id)
                    @if($editingPirepId === $p->id)
                        <div class="card rounded-t-none border-t-0 p-5 space-y-3 text-sm">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Edit PIREP</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-xs text-slate-400">Flight Time (hours)</label>
                                    <input wire:model="editFlightTime" type="number" step="0.1" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs text-slate-400">Landing Rate (fpm)</label>
                                    <input wire:model="editLandingRate" type="number" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs text-slate-400">Route String</label>
                                <input wire:model="editRoute" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs text-slate-400">Flight Log</label>
                                <textarea wire:model="editLog" rows="4" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="updatePirep" class="btn-primary text-sm px-4 py-1.5">Save Changes</button>
                                <button wire:click="cancelEdit" class="px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">Cancel</button>
                            </div>
                        </div>
                    @else
                        <div class="card rounded-t-none border-t-0 p-5 space-y-3 text-sm">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div><span class="text-xs text-slate-400">Flight</span><p class="font-medium text-slate-900 dark:text-white">{{ $p->flight_number }}</p></div>
                                <div><span class="text-xs text-slate-400">Route</span><p class="font-medium text-slate-900 dark:text-white">{{ $p->departure }} &rarr; {{ $p->arrival }}</p></div>
                                <div><span class="text-xs text-slate-400">Time</span><p class="font-medium text-slate-900 dark:text-white">{{ number_format($p->flight_time, 2) }}h</p></div>
                                <div>
                                    <span class="text-xs text-slate-400">Landing Rate</span>
                                    @if($p->landing_rate !== null)
                                        @php $lr = abs($p->landing_rate); @endphp
                                        <p class="font-medium {{ $lr <= 150 ? 'text-green-500' : ($lr <= 300 ? 'text-yellow-400' : 'text-red-500') }}">
                                            {{ $p->landing_rate }} fpm
                                        </p>
                                    @else
                                        <p class="font-medium text-slate-500">N/A</p>
                                    @endif
                                </div>
                                <div><span class="text-xs text-slate-400">Aircraft</span><p class="font-medium text-slate-900 dark:text-white">{{ $p->aircraft_registration }} ({{ $p->aircraft_icao }})</p></div>
                                <div><span class="text-xs text-slate-400">Score</span><p class="font-medium text-slate-900 dark:text-white">{{ $p->score }}</p></div>
                                <div><span class="text-xs text-slate-400">Status</span><p class="font-medium">{{ ucfirst($p->status) }}</p></div>
                                <div><span class="text-xs text-slate-400">Submitted</span><p class="font-medium text-slate-900 dark:text-white">{{ $p->submitted_at ? (\Carbon\Carbon::parse($p->submitted_at)->format('d M Y H:i')) : (\Carbon\Carbon::parse($p->created_at)->format('d M Y H:i')) }}</p></div>
                            </div>
                            @if($p->route)
                                <div><span class="text-xs text-slate-400">Route String</span><p class="font-mono text-xs mt-1 text-slate-600 dark:text-slate-400">{{ $p->route }}</p></div>
                            @endif
                            @if($p->log)
                                <div><span class="text-xs text-slate-400">Flight Log</span><p class="mt-1 text-slate-600 dark:text-slate-400 whitespace-pre-wrap">{{ $p->log }}</p></div>
                            @endif
                            @if($p->rejection_reason)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3">
                                    <span class="text-xs font-semibold text-red-600 dark:text-red-400">Rejection Reason</span>
                                    <p class="mt-1 text-sm text-red-700 dark:text-red-400">{{ $p->rejection_reason }}</p>
                                </div>
                            @endif
                            @if($p->status === 'pending')
                                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                                    <button wire:click="editPirep({{ $p->id }})" class="text-xs text-crimson-600 dark:text-crimson-400 hover:underline font-medium">Edit PIREP</button>
                                    <a href="{{ route('pireps.export', $p->id) }}" class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium">Download</a>
                                </div>
                            @else
                                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                                    <a href="{{ route('pireps.export', $p->id) }}" class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium">Download as Text</a>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400 dark:text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <p>No PIREPs found</p>
                <a href="{{ route('file-pirep') }}" wire:navigate class="btn-primary mt-4 inline-flex">File your first PIREP</a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($pireps->hasPages())
        <div class="mt-4">
            {{ $pireps->links() }}
        </div>
    @endif
</div>
