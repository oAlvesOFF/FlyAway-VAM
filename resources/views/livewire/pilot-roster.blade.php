<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search   = '';
    public string $sortBy   = 'total_hours';
    public string $sortDir  = 'desc';

    public function updatingSearch(): void { $this->resetPage(); }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
        $this->resetPage();
    }

    public function with(): array
    {
        $query = User::with(['rank', 'achievements'])
            ->withCount('achievements')
            ->where('status', 'active');

        if ($this->sortBy === 'achievements_count') {
            $query->orderBy('achievements_count', $this->sortDir);
        } else {
            $query->orderBy($this->sortBy, $this->sortDir);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name',     'like', '%' . $this->search . '%')
                  ->orWhere('pilot_id','like', '%' . $this->search . '%');
            });
        }

        return ['pilots' => $query->paginate(25)];
    }
}; ?>

@push('styles')
<style>
/* ══ Pilot Roster — Dynamic Theme Colors ══════════════════════ */
:root {
    --bg-page: #f8f9fa;
    --bg-card: #ffffff;
    --bg-header: #f1f3f5;
    --border-card: #e9ebec;
    --text-primary: #212529;
    --text-secondary: #495057;
    --text-muted: #868e96;
    --bg-badge: rgba(81, 140, 229, 0.1);
    --border-badge: rgba(81, 140, 229, 0.25);
    --text-badge: #518ce5;
    --bg-search: #ffffff;
    --border-search: #ced4da;
    --text-search: #212529;
    --shadow-card: 0 2px 4px rgba(0, 0, 0, .04);
}

.dark {
    --bg-page: #1a1d2e;
    --bg-card: #2b2f3e;
    --bg-header: #23263a;
    --border-card: #3a3f54;
    --text-primary: #e2e8f0;
    --text-secondary: #a0a8c0;
    --text-muted: #6b7280;
    --bg-badge: rgba(81, 140, 229, 0.18);
    --border-badge: rgba(81, 140, 229, 0.35);
    --text-badge: #518ce5;
    --bg-search: #23263a;
    --border-search: #3a3f54;
    --text-search: #e2e8f0;
    --shadow-card: none;
}

body, main { background: var(--bg-page) !important; }

.pr-wrap {
    padding: 24px;
    max-width: 1300px;
    margin: 0 auto;
    background: var(--bg-page);
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
}

/* ── Page title ─────────────────────────────────────────────── */
.pr-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    padding-bottom: 12px;
    border-bottom: 2px solid var(--text-badge);
    margin-bottom: 20px;
    width: fit-content;
}

/* ── Search bar ─────────────────────────────────────────────── */
.pr-search {
    background: var(--bg-search);
    border: 1px solid var(--border-search);
    border-radius: 6px;
    padding: 7px 14px;
    font-size: 13px;
    color: var(--text-search);
    outline: none;
    width: 280px;
    transition: border-color .2s;
}
.pr-search::placeholder { color: var(--text-muted); }
.pr-search:focus { border-color: var(--text-badge); }

/* ── Table card ─────────────────────────────────────────────── */
.pr-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    box-shadow: var(--shadow-card);
    overflow: hidden;
}

/* ── Table ──────────────────────────────────────────────────── */
.pr-table { width: 100%; border-collapse: collapse; }

.pr-table thead tr { background: var(--bg-header); }

.pr-table th {
    padding: 12px 14px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-badge);
    white-space: nowrap;
    border-bottom: 1px solid var(--border-card);
    user-select: none;
}
.pr-table th.sortable {
    cursor: pointer;
    transition: opacity .15s;
}
.pr-table th.sortable:hover { opacity: 0.85; }

.pr-table td {
    padding: 12px 14px;
    font-size: 13px;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--border-card);
    vertical-align: middle;
}
.pr-table tbody tr:last-child td { border-bottom: 0; }
.pr-table tbody tr:hover td { background: rgba(81, 140, 229, .05); }

/* ── Avatar cell ────────────────────────────────────────────── */
.pr-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: var(--bg-header);
    border: 1px solid var(--border-card);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 700;
    color: var(--text-badge); overflow: hidden; flex-shrink: 0;
}
.pr-avatar img { width: 100%; height: 100%; object-fit: cover; }

/* ── Name cell ──────────────────────────────────────────────── */
.pr-pilot-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-badge);
    text-decoration: none;
    transition: color .15s;
}
.pr-pilot-name:hover { color: #80aef2; text-decoration: underline; }
.pr-pilot-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ── Rank visuals ────────────────────────────────────── */
.pr-rank-pips { display: flex; gap: 2px; }
.pr-rank-pip {
    width: 6px; height: 18px; border-radius: 2px;
    background: var(--text-badge);
}
.pr-rank-pip.off { background: var(--border-card); }
.pr-rank-label { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
.pr-rank-base  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ── Icon stat cells ────────────────────────────────────────── */
.pr-icon-stat {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; color: var(--text-secondary);
    white-space: nowrap;
}
.pr-icon-stat i { font-size: 16px; color: var(--text-muted); }

/* ── Home airport badge ─────────────────────────────────────── */
.pr-airport-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--bg-badge);
    border: 1px solid var(--border-badge);
    color: var(--text-badge);
    border-radius: 4px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 700;
}

/* ── Pagination ─────────────────────────────────────────────── */
.pr-pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px;
    border-top: 1px solid var(--border-card);
    font-size: 12px; color: var(--text-muted);
}
.pr-page-btn {
    padding: 5px 14px; border-radius: 5px; font-size: 12px; font-weight: 600;
    border: 1px solid var(--border-card); cursor: pointer; transition: all .15s;
    background: var(--bg-card); color: var(--text-secondary);
}
.pr-page-btn:hover:not(:disabled) { background: var(--text-badge); color: #fff; border-color: var(--text-badge); }
.pr-page-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── Empty state ────────────────────────────────────────────── */
.pr-empty {
    text-align: center;
    padding: 64px 24px;
    color: var(--text-muted);
}
.pr-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: var(--border-card); }
</style>
@endpush

<div class="pr-wrap">

    {{-- Title ──────────────────────────────────────────────── --}}
    <div class="pr-title">
        <i class="ph-fill ph-users" style="font-size:20px;color:var(--text-badge);"></i>
        Pilots
    </div>

    {{-- Toolbar ─────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <input wire:model.live.debounce="search"
               placeholder="Search by name or pilot ID…"
               class="pr-search">
        <span style="font-size:12px;color:var(--text-muted);">
            <strong style="color:var(--text-secondary);">{{ $pilots->total() }}</strong> pilots
        </span>
    </div>

    {{-- Table card ──────────────────────────────────────────── --}}
    <div class="pr-card">
        <div style="overflow-x:auto;">
            <table class="pr-table">
                <thead>
                    <tr>
                        {{-- ID --}}
                        <th class="sortable" wire:click="sort('pilot_id')" style="width:70px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                ID
                                @if($sortBy === 'pilot_id')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                        {{-- Name --}}
                        <th class="sortable" wire:click="sort('name')">
                            <div style="display:flex; align-items:center; gap:6px;">
                                Name
                                @if($sortBy === 'name')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                        {{-- Pilot rank --}}
                        <th class="sortable" wire:click="sort('rank_id')">
                            <div style="display:flex; align-items:center; gap:6px;">
                                Pilot rank
                                @if($sortBy === 'rank_id')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                        {{-- Awards --}}
                        <th class="sortable" wire:click="sort('achievements_count')">
                            <div style="display:flex; align-items:center; gap:6px;">
                                Awards
                                @if($sortBy === 'achievements_count')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                        {{-- Hours --}}
                        <th class="sortable" wire:click="sort('total_hours')">
                            <div style="display:flex; align-items:center; gap:6px;">
                                Hours
                                @if($sortBy === 'total_hours')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                        {{-- Flights --}}
                        <th class="sortable" wire:click="sort('total_flights')">
                            <div style="display:flex; align-items:center; gap:6px;">
                                Flights
                                @if($sortBy === 'total_flights')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                        {{-- Home Airport --}}
                        <th class="sortable" wire:click="sort('home_airport')">
                            <div style="display:flex; align-items:center; gap:6px;">
                                Home Airport
                                @if($sortBy === 'home_airport')
                                    <i class="ph-bold ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="ph ph-caret-up-down" style="opacity: 0.4;"></i>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pilots as $pilot)
                    @php
                        $hours   = (int) $pilot->total_hours;
                        $minutes = (int) round(($pilot->total_hours - $hours) * 60);
                        $lastPirep = $pilot->pireps()->latest()->first();
                        $rankLevel = $pilot->rank?->level ?? 1;
                        $awardCount = $pilot->achievements->count();
                    @endphp
                    <tr>
                        {{-- ID + Avatar --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="pr-avatar">
                                    @if($pilot->avatar)
                                        <img src="{{ Storage::url($pilot->avatar) }}" alt="">
                                    @else
                                        <i class="ph-fill ph-user-circle" style="font-size:26px;color:var(--text-muted);"></i>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Name + flag --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                                {{-- Country flag emoji --}}
                                @if($pilot->country)
                                    @php
                                        $cc = strtoupper($pilot->country);
                                        $flag = implode('', array_map(fn($c) => mb_chr(127397 + ord($c)), str_split($cc)));
                                    @endphp
                                    <span style="font-size:15px;" title="{{ $pilot->country }}">{{ $flag }}</span>
                                @endif
                                <a href="{{ route('pilot-profile', $pilot->id) }}"
                                   wire:navigate
                                   class="pr-pilot-name">{{ $pilot->name }}</a>
                            </div>
                            <div class="pr-pilot-sub">
                                @if($lastPirep)
                                    Your last flight was {{ $lastPirep->created_at->diffForHumans() }}.
                                @else
                                    Did not any flight.
                                @endif
                            </div>
                        </td>

                        {{-- Rank visuals --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($pilot->rank?->image)
                                    <img src="{{ Str::startsWith($pilot->rank->image, ['http://', 'https://', '/']) ? $pilot->rank->image : Storage::url($pilot->rank->image) }}"
                                         alt=""
                                         style="height:20px;max-width:80px;object-fit:contain;border-radius:2px;">
                                @else
                                    <div class="pr-rank-pips">
                                        @for($i=0;$i<4;$i++)
                                            <div class="pr-rank-pip {{ $i >= $rankLevel ? 'off' : '' }}"></div>
                                        @endfor
                                    </div>
                                @endif
                                <div>
                                    <div class="pr-rank-label">{{ $pilot->rank?->name ?? 'New Pilot' }}</div>
                                    @if($pilot->home_airport)
                                        <div class="pr-rank-base">Base: <span style="color:var(--text-badge);font-weight:600;">{{ $pilot->home_airport }}</span></div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Awards --}}
                        <td>
                            <div class="pr-icon-stat">
                                <i class="ph-fill ph-trophy"></i>
                                {{ $awardCount }}
                            </div>
                        </td>

                        {{-- Hours --}}
                        <td>
                            <div class="pr-icon-stat">
                                <i class="ph-fill ph-clock"></i>
                                {{ $hours }}h {{ str_pad($minutes,2,'0',STR_PAD_LEFT) }}m
                            </div>
                        </td>

                        {{-- Flights --}}
                        <td>
                            <div class="pr-icon-stat">
                                <i class="ph-fill ph-airplane-tilt"></i>
                                {{ $pilot->total_flights }}
                            </div>
                        </td>

                        {{-- Home Airport badge --}}
                        <td>
                            @if($pilot->home_airport)
                                <span class="pr-airport-badge">
                                    <i class="ph-fill ph-house" style="font-size:11px;"></i>
                                    {{ $pilot->home_airport }}
                                </span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="pr-empty">
                                <i class="ph-fill ph-users-three"></i>
                                <div style="font-size:15px;font-weight:700;color:var(--text-muted);margin-bottom:6px;">No pilots found</div>
                                <div style="font-size:13px;">Try a different search term.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination ───────────────────────────────────────── --}}
        @if($pilots->hasPages())
        <div class="pr-pagination">
            <span>
                Showing <strong style="color:var(--text-secondary);">{{ $pilots->firstItem() }}</strong>–<strong style="color:var(--text-secondary);">{{ $pilots->lastItem() }}</strong>
                of <strong style="color:var(--text-secondary);">{{ $pilots->total() }}</strong> pilots
            </span>
            <div style="display:flex;gap:8px;">
                <button class="pr-page-btn"
                        {{ $pilots->onFirstPage() ? 'disabled' : '' }}
                        wire:click="previousPage">
                    <i class="ph-fill ph-caret-left"></i> Previous
                </button>
                <button class="pr-page-btn"
                        {{ !$pilots->hasMorePages() ? 'disabled' : '' }}
                        wire:click="nextPage">
                    Next <i class="ph-fill ph-caret-right"></i>
                </button>
            </div>
        </div>
        @endif
    </div>

</div>
