<?php

use App\Models\Pirep;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
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

@push('styles')
<style>
/* ══ SPTheme Dynamic Dark/Light Mode ══════════════════════ */
:root {
    --bg-page: #f8f9fa;
    --bg-card: #ffffff;
    --bg-header: #f1f3f5;
    --bg-header-hover: rgba(81, 140, 229, 0.05);
    --border-card: #e9ebec;
    --text-primary: #212529;
    --text-secondary: #495057;
    --text-muted: #868e96;
    --text-badge: #518ce5;
    --shadow-card: 0 2px 4px rgba(0, 0, 0, .04);
}

.dark {
    --bg-page: #1a1d2e;
    --bg-card: #2b2f3e;
    --bg-header: #23263a;
    --bg-header-hover: rgba(81, 140, 229, 0.08);
    --border-card: #3a3f54;
    --text-primary: #e2e8f0;
    --text-secondary: #a0a8c0;
    --text-muted: #6b7280;
    --text-badge: #518ce5;
    --shadow-card: none;
}

body, main { background: var(--bg-page) !important; }

.sp-wrap {
    padding: 24px;
    max-width: 1300px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
}

.sp-title-area {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
    margin-bottom: 24px;
}
.sp-title {
    display: flex; align-items: center; gap: 10px;
    font-size: 18px; font-weight: 700; color: var(--text-primary);
    padding-bottom: 12px; border-bottom: 2px solid var(--text-badge);
}

.sp-btn-primary {
    background: var(--text-badge); color: #fff;
    padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;
    transition: all .2s; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
}
.sp-btn-primary:hover { opacity: 0.9; }

.sp-tabs {
    display: flex; gap: 8px; margin-bottom: 16px;
    overflow-x: auto;
}
.sp-tab {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    color: var(--text-secondary);
    padding: 7px 16px; border-radius: 6px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all .2s; white-space: nowrap;
}
.sp-tab:hover { border-color: var(--text-badge); color: var(--text-badge); }
.sp-tab.active { background: var(--text-badge); border-color: var(--text-badge); color: #fff; }

.sp-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    box-shadow: var(--shadow-card);
    overflow: hidden;
}

.sp-table { width: 100%; border-collapse: collapse; }
.sp-table thead tr { background: var(--bg-header); border-bottom: 1px solid var(--border-card); }
.sp-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: var(--text-badge); white-space: nowrap; }
.sp-table td { padding: 14px 16px; font-size: 13px; color: var(--text-secondary); border-bottom: 1px solid var(--border-card); vertical-align: middle; }
.sp-table tbody tr.main-row { cursor: pointer; transition: background .15s; }
.sp-table tbody tr.main-row:hover { background: var(--bg-header-hover); }

.sp-table td.strong { color: var(--text-badge); font-weight: 600; }
.sp-icon-stat { display: flex; align-items: center; gap: 6px; }
.sp-icon-stat i { font-size: 16px; color: var(--text-muted); }

/* Badges */
.sp-badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-block; }
.sp-badge.approved { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.sp-badge.pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.sp-badge.rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

/* Details row */
.sp-details-row td { padding: 0 !important; border-bottom: 1px solid var(--border-card); }
.sp-details { background: var(--bg-header); padding: 20px; border-left: 3px solid var(--text-badge); }
.sp-details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 16px; }
.sp-detail-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
.sp-detail-label i { font-size: 14px; }
.sp-detail-val { color: var(--text-primary); font-size: 13px; font-weight: 500; }

.sp-input { width: 100%; background: var(--bg-card); border: 1px solid var(--border-card); color: var(--text-primary); padding: 8px 12px; border-radius: 6px; font-size: 13px; }
.sp-input:focus { outline: none; border-color: var(--text-badge); }

.sp-empty { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.sp-empty i { font-size: 48px; color: var(--border-card); margin-bottom: 12px; }

.sp-pagination { padding: 12px 16px; border-top: 1px solid var(--border-card); }
</style>
@endpush

<div class="sp-wrap">
    <div class="sp-title-area">
        <div class="sp-title">
            <i class="ph-fill ph-airplane-tilt"></i> My PIREPs
        </div>
        <a href="{{ route('file-pirep') }}" wire:navigate class="sp-btn-primary">
            <i class="ph-bold ph-plus"></i> File New PIREP
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="sp-tabs">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')" class="sp-tab {{ $filter === $key ? 'active' : '' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="sp-card">
        <div style="overflow-x:auto;">
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Flight</th>
                        <th>Route</th>
                        <th>Aircraft</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pireps as $p)
                        <tr class="main-row" wire:click="toggleDetail({{ $p->id }})">
                            <td class="strong">{{ $p->flight_number }}</td>
                            <td>
                                <div class="sp-icon-stat">
                                    <i class="ph-fill ph-map-pin"></i>
                                    {{ $p->departure }} &rarr; {{ $p->arrival }}
                                </div>
                            </td>
                            <td>{{ $p->aircraft_icao }}</td>
                            <td>
                                <div class="sp-icon-stat">
                                    <i class="ph-fill ph-clock"></i>
                                    {{ number_format($p->flight_time, 2) }}h
                                </div>
                            </td>
                            <td>
                                <span class="sp-badge {{ $p->status }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    {{ $p->submitted_at ? \Carbon\Carbon::parse($p->submitted_at)->format('d M Y') : \Carbon\Carbon::parse($p->created_at)->format('d M Y') }}
                                    <i class="ph-bold ph-caret-{{ $selectedPirepId === $p->id ? 'up' : 'down' }}" style="color:var(--text-muted);"></i>
                                </div>
                            </td>
                        </tr>
                        @if($selectedPirepId === $p->id)
                            <tr class="sp-details-row">
                                <td colspan="6">
                                    <div class="sp-details">
                                        @if($editingPirepId === $p->id)
                                            <div style="margin-bottom:16px;font-size:15px;font-weight:700;color:var(--text-primary);">Edit PIREP</div>
                                            <div class="sp-details-grid">
                                                <div>
                                                    <div class="sp-detail-label">Flight Time (hours)</div>
                                                    <input wire:model="editFlightTime" type="number" step="0.1" class="sp-input">
                                                </div>
                                                <div>
                                                    <div class="sp-detail-label">Landing Rate (fpm)</div>
                                                    <input wire:model="editLandingRate" type="number" class="sp-input">
                                                </div>
                                                <div style="grid-column: 1 / -1;">
                                                    <div class="sp-detail-label">Route String</div>
                                                    <input wire:model="editRoute" type="text" class="sp-input">
                                                </div>
                                                <div style="grid-column: 1 / -1;">
                                                    <div class="sp-detail-label">Flight Log</div>
                                                    <textarea wire:model="editLog" rows="3" class="sp-input"></textarea>
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:10px;">
                                                <button wire:click="updatePirep" class="sp-btn-primary">Save Changes</button>
                                                <button wire:click="cancelEdit" class="sp-tab">Cancel</button>
                                            </div>
                                        @else
                                            <div class="sp-details-grid">
                                                <div>
                                                    <div class="sp-detail-label"><i class="ph-fill ph-airplane-landing"></i> Landing Rate</div>
                                                    <div class="sp-detail-val">
                                                        @if($p->landing_rate !== null)
                                                            @php $lr = abs($p->landing_rate); @endphp
                                                            <span style="color: {{ $lr <= 150 ? '#10b981' : ($lr <= 300 ? '#f59e0b' : '#ef4444') }}">
                                                                {{ $p->landing_rate }} fpm
                                                            </span>
                                                        @else
                                                            <span style="color:var(--text-muted);">N/A</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="sp-detail-label"><i class="ph-fill ph-star"></i> Score</div>
                                                    <div class="sp-detail-val">{{ $p->score }}</div>
                                                </div>
                                                <div>
                                                    <div class="sp-detail-label"><i class="ph-fill ph-airplane-tilt"></i> Registration</div>
                                                    <div class="sp-detail-val">{{ $p->aircraft_registration }}</div>
                                                </div>
                                            </div>
                                            
                                            @if($p->route)
                                                <div style="margin-top:16px;">
                                                    <div class="sp-detail-label"><i class="ph-fill ph-map-trifold"></i> Route String</div>
                                                    <div class="sp-detail-val" style="font-family:monospace;background:var(--bg-card);padding:10px;border-radius:4px;border:1px solid var(--border-card);">
                                                        {{ $p->route }}
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($p->log)
                                                <div style="margin-top:16px;">
                                                    <div class="sp-detail-label"><i class="ph-fill ph-article"></i> Flight Log</div>
                                                    <div class="sp-detail-val" style="white-space:pre-wrap;font-size:12px;background:var(--bg-card);padding:10px;border-radius:4px;border:1px solid var(--border-card);">{{ $p->log }}</div>
                                                </div>
                                            @endif

                                            @if($p->rejection_reason)
                                                <div style="margin-top:16px;background:rgba(239,68,68,0.1);padding:12px;border-radius:6px;border:1px solid rgba(239,68,68,0.2);">
                                                    <div class="sp-detail-label" style="color:#ef4444;"><i class="ph-fill ph-warning-circle"></i> Rejection Reason</div>
                                                    <div style="color:#ef4444;font-size:13px;font-weight:500;">{{ $p->rejection_reason }}</div>
                                                </div>
                                            @endif

                                            <div style="margin-top:20px;display:flex;gap:12px;align-items:center;border-top:1px solid var(--border-card);padding-top:16px;">
                                                @if($p->status === 'pending')
                                                    <button wire:click="editPirep({{ $p->id }})" class="sp-btn-primary" style="padding:6px 12px;font-size:12px;">Edit PIREP</button>
                                                @endif
                                                <a href="{{ route('pireps.export', $p->id) }}" style="font-size:12px;font-weight:600;color:var(--text-muted);text-decoration:none;display:flex;align-items:center;gap:4px;">
                                                    <i class="ph-bold ph-download-simple"></i> Download
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="sp-empty">
                                    <i class="ph-fill ph-airplane-tilt"></i>
                                    <div style="font-size:15px;font-weight:700;color:var(--text-primary);">No PIREPs Found</div>
                                    <div style="font-size:13px;margin-top:4px;">You haven't filed any PIREPs matching this filter yet.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pireps->hasPages())
            <div class="sp-pagination">
                {{ $pireps->links() }}
            </div>
        @endif
    </div>
</div>
