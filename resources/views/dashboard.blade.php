@extends('layouts.app', ['title' => 'Dashboard'])

@php $chartPireps = auth()->user()->pireps()->latest()->limit(10)->get(['status', 'score', 'flight_number', 'created_at']); @endphp

@push('styles')
<style>
/* ══ Dashboard — Dynamic Theme Colors ══════════════════════════ */
:root {
    --bg-page: #f1f3f7;
    --bg-card: #ffffff;
    --bg-header: #f1f3f5;
    --border-card: #e5e7eb;
    --text-primary: #111827;
    --text-secondary: #374151;
    --text-muted: #9ca3af;
    --bg-badge: rgba(81, 140, 229, 0.1);
    --border-badge: rgba(81, 140, 229, 0.25);
    --text-badge: #518ce5;
    --shadow-card: 0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
    --hover-lift: 0 4px 16px rgba(0,0,0,.10);
}

.dark {
    --bg-page: #13162b;
    --bg-card: #1e2235;
    --bg-header: #191c2f;
    --border-card: #2e3350;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #4b5563;
    --bg-badge: rgba(81, 140, 229, 0.15);
    --border-badge: rgba(81, 140, 229, 0.3);
    --text-badge: #6ea8f7;
    --shadow-card: 0 1px 3px rgba(0,0,0,.3);
    --hover-lift: 0 6px 24px rgba(0,0,0,.35);
}

body, main { background: var(--bg-page) !important; }

.db-wrap {
    padding: 28px 32px;
    max-width: 1400px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
}

/* ── Welcome Banner ──────────────────────────────────────────── */
.db-banner {
    background: linear-gradient(135deg, #1a2a6c 0%, #2553a0 50%, #518ce5 100%);
    border-radius: 14px;
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}
.db-banner::before {
    content: '';
    position: absolute;
    top: -60px; right: -80px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
}
.db-banner::after {
    content: '';
    position: absolute;
    bottom: -80px; right: 120px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.db-banner-left { position: relative; z-index: 1; }
.db-banner-label { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 8px; }
.db-banner-name { font-size: 28px; font-weight: 800; color: #fff; line-height: 1.2; }
.db-banner-sub { font-size: 14px; color: rgba(255,255,255,0.65); margin-top: 6px; }
.db-banner-right { display: flex; gap: 12px; position: relative; z-index: 1; }
.db-banner-badge {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    padding: 12px 20px;
    text-align: center;
    min-width: 80px;
    backdrop-filter: blur(6px);
}
.db-banner-badge-val { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; }
.db-banner-badge-lbl { font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: .06em; margin-top: 4px; }

/* ── Stats Row ───────────────────────────────────────────────── */
.db-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
@media (max-width: 900px) { .db-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .db-stats { grid-template-columns: 1fr; } }

.db-stat {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--shadow-card);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: box-shadow .2s, transform .2s;
    cursor: default;
}
.db-stat:hover { box-shadow: var(--hover-lift); transform: translateY(-2px); }
.db-stat-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.db-stat-icon.blue  { background: rgba(81,140,229,0.12); color: #518ce5; }
.db-stat-icon.green { background: rgba(16,185,129,0.12); color: #10b981; }
.db-stat-icon.amber { background: rgba(245,158,11,0.12); color: #f59e0b; }
.db-stat-icon.red   { background: rgba(239,68,68,0.12); color: #ef4444; }
.db-stat-icon.indigo { background: rgba(99,102,241,0.12); color: #6366f1; }
.db-stat-val { font-size: 22px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.db-stat-lbl { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .07em; margin-top: 5px; }

/* ── Main Grid ───────────────────────────────────────────────── */
.db-main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
@media (max-width: 900px) { .db-main-grid { grid-template-columns: 1fr; } }

/* ── Card Base ───────────────────────────────────────────────── */
.db-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 12px;
    box-shadow: var(--shadow-card);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.db-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-card);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.db-card-title {
    font-size: 13px; font-weight: 700;
    color: var(--text-primary);
    display: flex; align-items: center; gap: 8px;
}
.db-card-title i { color: var(--text-badge); font-size: 16px; }
.db-card-body { padding: 20px; flex: 1; }
.db-card-link { font-size: 12px; font-weight: 600; color: var(--text-badge); text-decoration: none; }
.db-card-link:hover { opacity: 0.8; }

/* ── Status Badge ────────────────────────────────────────────── */
.db-status-badge {
    padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block;
}
.db-status-badge.scheduled { background: rgba(99,102,241,0.12); color: #6366f1; border: 1px solid rgba(99,102,241,0.25); }

/* ── Next Flight Card ────────────────────────────────────────── */
.db-route-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: var(--bg-header);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 16px;
}
.db-icao { font-size: 30px; font-weight: 900; color: var(--text-primary); letter-spacing: -1px; }
.db-city { font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 4px; }
.db-path-mid {
    flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.db-path-num { font-size: 11px; font-weight: 700; color: var(--text-badge); }
.db-path-line-wrap { width: 100%; display: flex; align-items: center; gap: 4px; }
.db-path-line { flex: 1; border-top: 2px dashed var(--border-card); }
.db-path-plane {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--text-badge); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
    box-shadow: 0 4px 12px rgba(81,140,229,0.35);
}
.db-path-time { font-size: 11px; font-weight: 700; color: var(--text-muted); }
.db-flight-info { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.db-info-chip {
    flex: 1; min-width: 100px; background: var(--bg-header);
    border: 1px solid var(--border-card); border-radius: 8px; padding: 10px 14px;
    font-size: 12px;
}
.db-info-chip-lbl { color: var(--text-muted); font-weight: 600; margin-bottom: 4px; }
.db-info-chip-val { color: var(--text-primary); font-weight: 700; font-size: 14px; }
.db-action-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 12px; border-radius: 8px;
    background: var(--text-badge); color: #fff;
    font-size: 13px; font-weight: 700; text-decoration: none;
    border: none; cursor: pointer; transition: opacity .2s;
}
.db-action-btn:hover { opacity: 0.9; }

/* ── PIREPs Table ────────────────────────────────────────────── */
.db-pirep-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-card);
    transition: background .15s; cursor: pointer;
}
.db-pirep-row:last-child { border-bottom: none; }
.db-pirep-row:hover { margin: 0 -20px; padding: 12px 20px; background: var(--bg-header); border-radius: 6px; }
.db-pirep-fn { font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
.db-pirep-route { font-size: 11px; color: var(--text-muted); font-weight: 500; }
.db-pirep-time { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.db-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.db-badge.approved { background: rgba(16,185,129,0.12); color: #10b981; }
.db-badge.pending  { background: rgba(245,158,11,0.12); color: #f59e0b; }
.db-badge.rejected { background: rgba(239,68,68,0.12); color: #ef4444; }

/* ── Progression Bar ─────────────────────────────────────────── */
.db-prog-wrap { margin-bottom: 20px; }
.db-prog-labels { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; margin-bottom: 10px; }
.db-prog-cur { color: var(--text-muted); }
.db-prog-next { color: var(--text-badge); }
.db-prog-bar {
    height: 10px; background: var(--bg-header);
    border-radius: 99px; overflow: hidden;
    border: 1px solid var(--border-card);
}
.db-prog-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #518ce5, #6ea8f7); transition: width .6s ease; }
.db-prog-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
.db-prog-hint { font-size: 12px; color: var(--text-muted); }
.db-prog-unlock { font-size: 11px; font-weight: 700; background: var(--bg-badge); color: var(--text-badge); border: 1px solid var(--border-badge); padding: 3px 10px; border-radius: 4px; }

/* ── Charts Row ──────────────────────────────────────────────── */
.db-charts-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    margin-bottom: 20px;
}
@media (max-width: 900px) { .db-charts-grid { grid-template-columns: 1fr; } }

/* ── Empty ───────────────────────────────────────────────────── */
.db-empty { text-align: center; padding: 40px 20px; color: var(--text-muted); }
.db-empty i { font-size: 40px; color: var(--border-card); display: block; margin-bottom: 12px; }
.db-empty-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="db-wrap">

    @if(auth()->user()->status === 'suspended')
        <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;">
            <i class="ph-fill ph-warning-circle" style="font-size: 22px; color: #ef4444; flex-shrink: 0;"></i>
            <div>
                <div style="font-weight: 700; color: #ef4444; margin-bottom: 4px;">Account Suspended</div>
                @if(auth()->user()->suspension_reason)
                    <div style="font-size: 13px; color: #ef4444; opacity: 0.85;">{{ auth()->user()->suspension_reason }}</div>
                @endif
                <div style="font-size: 12px; color: #ef4444; opacity: 0.65; margin-top: 6px;">Please contact staff to resolve this issue.</div>
            </div>
        </div>
    @endif

    {{-- ══ Welcome Banner ══ --}}
    <div class="db-banner">
        <div class="db-banner-left">
            <div class="db-banner-label"><i class="ph-fill ph-hand-waving"></i> &nbsp;Welcome back</div>
            <div class="db-banner-name">{{ auth()->user()->name }}</div>
            <div class="db-banner-sub">
                <i class="ph-fill ph-medal" style="margin-right:4px;"></i>{{ auth()->user()->rank?->name ?? 'Candidate' }}
                &nbsp;&bull;&nbsp;
                <i class="ph-fill ph-map-pin" style="margin-right:4px;"></i>{{ auth()->user()->last_location ?? 'No location' }}
                &nbsp;&bull;&nbsp;
                <i class="ph-fill ph-identification-card" style="margin-right:4px;"></i>{{ auth()->user()->pilot_id ?? 'N/A' }}
            </div>
        </div>
        <div class="db-banner-right">
            <div class="db-banner-badge">
                <div class="db-banner-badge-val">{{ number_format(auth()->user()->total_hours, 1) }}</div>
                <div class="db-banner-badge-lbl">Total Hours</div>
            </div>
            <div class="db-banner-badge">
                <div class="db-banner-badge-val">{{ auth()->user()->total_flights }}</div>
                <div class="db-banner-badge-lbl">Flights</div>
            </div>
            @php
                $approvedCount = auth()->user()->pireps()->where('status','approved')->count();
                $avgScore = auth()->user()->pireps()->where('status','approved')->avg('score');
            @endphp
            <div class="db-banner-badge">
                <div class="db-banner-badge-val">{{ $approvedCount }}</div>
                <div class="db-banner-badge-lbl">Approved</div>
            </div>
            <div class="db-banner-badge">
                <div class="db-banner-badge-val">{{ $avgScore ? number_format($avgScore, 0) : '—' }}</div>
                <div class="db-banner-badge-lbl">Avg Score</div>
            </div>
        </div>
    </div>

    {{-- ══ Stats Row ══ --}}
    @php
        $currentHours = auth()->user()->total_hours;
        $nextRank = \App\Models\Rank::where('minimum_hours', '>', $currentHours)->orderBy('minimum_hours')->first();
        $currentRankMin = auth()->user()->rank?->minimum_hours ?? 0;
        $progress = ($nextRank && $nextRank->minimum_hours > $currentRankMin)
            ? min(100, (($currentHours - $currentRankMin) / ($nextRank->minimum_hours - $currentRankMin)) * 100)
            : 100;
        $hoursToNext = $nextRank ? number_format($nextRank->minimum_hours - $currentHours, 1) : 0;
    @endphp

    <div class="db-stats">
        <div class="db-stat">
            <div class="db-stat-icon blue"><i class="ph-fill ph-clock"></i></div>
            <div>
                <div class="db-stat-val">{{ number_format(auth()->user()->total_hours, 1) }}h</div>
                <div class="db-stat-lbl">Total Hours</div>
            </div>
        </div>
        <div class="db-stat">
            <div class="db-stat-icon green"><i class="ph-fill ph-airplane-tilt"></i></div>
            <div>
                <div class="db-stat-val">{{ auth()->user()->total_flights }}</div>
                <div class="db-stat-lbl">Flights Logged</div>
            </div>
        </div>
        <div class="db-stat">
            <div class="db-stat-icon amber"><i class="ph-fill ph-medal"></i></div>
            <div>
                <div class="db-stat-val" style="font-size: 15px;">{{ auth()->user()->rank?->name ?? 'Candidate' }}</div>
                <div class="db-stat-lbl">Current Rank</div>
            </div>
        </div>
        <div class="db-stat">
            <div class="db-stat-icon indigo"><i class="ph-fill ph-map-pin"></i></div>
            <div>
                <div class="db-stat-val" style="font-family: monospace;">{{ auth()->user()->last_location ?? '—' }}</div>
                <div class="db-stat-lbl">Location</div>
            </div>
        </div>
    </div>

    {{-- ══ Next Flight + Recent PIREPs ══ --}}
    <div class="db-main-grid">

        {{-- Next Flight --}}
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title">
                    <i class="ph-fill ph-calendar-check"></i> Next Flight
                </div>
                <span class="db-status-badge scheduled">Scheduled</span>
            </div>
            <div class="db-card-body">
                @php $nextBid = auth()->user()->bids()->with('schedule')->latest()->first(); @endphp
                @if($nextBid && $nextBid->schedule)
                    <div class="db-route-wrap">
                        <div style="text-align: center;">
                            <div class="db-icao">{{ $nextBid->schedule->departure }}</div>
                            <div class="db-city">{{ $nextBid->schedule->departure }}</div>
                        </div>
                        <div class="db-path-mid">
                            <div class="db-path-num">{{ $nextBid->schedule->flight_number }}</div>
                            <div class="db-path-line-wrap">
                                <div class="db-path-line"></div>
                                <div class="db-path-plane"><i class="ph-fill ph-airplane-in-flight"></i></div>
                                <div class="db-path-line"></div>
                            </div>
                            <div class="db-path-time">{{ $nextBid->schedule->flight_time }}h</div>
                        </div>
                        <div style="text-align: center;">
                            <div class="db-icao">{{ $nextBid->schedule->arrival }}</div>
                            <div class="db-city">{{ $nextBid->schedule->arrival }}</div>
                        </div>
                    </div>

                    <div class="db-flight-info">
                        <div class="db-info-chip">
                            <div class="db-info-chip-lbl">Flight</div>
                            <div class="db-info-chip-val">{{ $nextBid->schedule->flight_number }}</div>
                        </div>
                        <div class="db-info-chip">
                            <div class="db-info-chip-lbl">Duration</div>
                            <div class="db-info-chip-val">{{ $nextBid->schedule->flight_time }}h</div>
                        </div>
                        @if($nextBid->schedule->aircraft_type)
                        <div class="db-info-chip">
                            <div class="db-info-chip-lbl">Aircraft</div>
                            <div class="db-info-chip-val">{{ $nextBid->schedule->aircraft_type }}</div>
                        </div>
                        @endif
                    </div>

                    <a href="{{ route('simbrief') }}" wire:navigate class="db-action-btn">
                        <i class="ph-bold ph-paper-plane-tilt"></i> View Briefing
                    </a>
                @else
                    <div class="db-empty">
                        <i class="ph-fill ph-calendar-x"></i>
                        <div class="db-empty-title">No upcoming flights</div>
                        <a href="{{ route('flights') }}" wire:navigate class="db-action-btn">
                            <i class="ph-bold ph-plus"></i> Book a Flight
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent PIREPs --}}
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title">
                    <i class="ph-fill ph-clock-counter-clockwise"></i> Recent PIREPs
                </div>
                <a href="{{ route('my-pireps') }}" wire:navigate class="db-card-link">View all →</a>
            </div>
            <div class="db-card-body">
                @php $recentPireps = auth()->user()->pireps()->latest()->take(5)->get(); @endphp
                @if($recentPireps->count() > 0)
                    @foreach($recentPireps as $pirep)
                        <div class="db-pirep-row" onclick="window.location.href='{{ route('my-pireps') }}'">
                            <div>
                                <div class="db-pirep-fn">{{ $pirep->flight_number }}</div>
                                <div class="db-pirep-route">{{ $pirep->departure }} &rarr; {{ $pirep->arrival }} &bull; {{ $pirep->created_at->diffForHumans() }}</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="db-pirep-time">{{ $pirep->flight_time }}h</div>
                                <span class="db-badge {{ $pirep->status }}">{{ ucfirst($pirep->status) }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="db-empty">
                        <i class="ph-fill ph-airplane-tilt"></i>
                        <div class="db-empty-title">No PIREPs submitted yet</div>
                        <a href="{{ route('file-pirep') }}" wire:navigate class="db-action-btn">File a PIREP</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Career Progression ══ --}}
    <div class="db-card" style="margin-bottom: 20px;">
        <div class="db-card-header">
            <div class="db-card-title">
                <i class="ph-fill ph-chart-line-up"></i> Career Progression
            </div>
            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">{{ number_format($progress, 1) }}% complete</span>
        </div>
        <div class="db-card-body">
            @if($nextRank)
                <div class="db-prog-wrap">
                    <div class="db-prog-labels">
                        <span class="db-prog-cur"><i class="ph-fill ph-medal" style="margin-right: 4px;"></i>{{ auth()->user()->rank?->name ?? 'Candidate' }}</span>
                        <span class="db-prog-next">{{ $nextRank->name }} <i class="ph-fill ph-arrow-right" style="margin-left: 2px;"></i></span>
                    </div>
                    <div class="db-prog-bar">
                        <div class="db-prog-fill" style="width: {{ $progress }}%;"></div>
                    </div>
                    <div class="db-prog-footer">
                        <span class="db-prog-hint">
                            <i class="ph-fill ph-clock" style="margin-right: 4px;"></i>
                            {{ $hoursToNext }} hours to next rank
                        </span>
                        @if($nextRank->allowed_categories)
                            @php $unlocks = array_diff(explode(',', $nextRank->allowed_categories), explode(',', auth()->user()->rank?->allowed_categories ?? '')); @endphp
                            @if(count($unlocks) > 0)
                                <span class="db-prog-unlock">Unlocks: {{ implode(', ', $unlocks) }}</span>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Rank milestones strip --}}
                @php $allRanks = \App\Models\Rank::orderBy('minimum_hours')->get(); @endphp
                @if($allRanks->count() > 0)
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @foreach($allRanks as $r)
                        @php $reached = $currentHours >= $r->minimum_hours; @endphp
                        <div style="flex: 1; min-width: 80px; text-align: center; padding: 10px 8px; border-radius: 8px; border: 1px solid var(--border-card); background: {{ $reached ? 'rgba(81,140,229,0.1)' : 'var(--bg-header)' }};">
                            <div style="font-size: 18px; color: {{ $reached ? 'var(--text-badge)' : 'var(--text-muted)' }};">
                                <i class="ph-fill {{ $reached ? 'ph-medal' : 'ph-lock' }}"></i>
                            </div>
                            <div style="font-size: 11px; font-weight: 700; color: {{ $reached ? 'var(--text-primary)' : 'var(--text-muted)' }}; margin-top: 4px;">{{ $r->name }}</div>
                            <div style="font-size: 10px; color: var(--text-muted);">{{ $r->minimum_hours }}h</div>
                        </div>
                    @endforeach
                </div>
                @endif
            @else
                <div class="db-empty">
                    <i class="ph-fill ph-star"></i>
                    <div class="db-empty-title">Maximum rank achieved!</div>
                    <div style="font-size: 13px;">You have reached the highest rank in the airline.</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ══ Charts ══ --}}
    <div class="db-charts-grid">
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title"><i class="ph-fill ph-chart-pie-slice"></i> PIREP Status</div>
            </div>
            <div class="db-card-body">
                <div style="height: 220px; position: relative;">
                    <canvas id="myPirepChart"></canvas>
                </div>
            </div>
        </div>

        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title"><i class="ph-fill ph-chart-bar"></i> Recent Flight Scores</div>
            </div>
            <div class="db-card-body">
                <div style="height: 220px; position: relative;">
                    <canvas id="myScoresChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pireps = @json($chartPireps);

    const statusCounts = { approved: 0, pending: 0, rejected: 0 };
    const scores = [];
    const scoreLabels = [];

    pireps.forEach(p => {
        statusCounts[p.status] = (statusCounts[p.status] || 0) + 1;
        scores.push(p.score || 0);
        scoreLabels.push(p.flight_number);
    });

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#6b7280';
    const gridColor = isDark ? '#2e3350' : '#e5e7eb';

    // Doughnut
    new Chart(document.getElementById('myPirepChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [statusCounts.approved || 0, statusCounts.pending || 0, statusCounts.rejected || 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: textColor, padding: 16, usePointStyle: true, pointStyle: 'circle', font: { size: 12, weight: '600' } }
                }
            },
            cutout: '68%'
        }
    });

    // Bar
    if (scores.length) {
        new Chart(document.getElementById('myScoresChart'), {
            type: 'bar',
            data: {
                labels: scoreLabels,
                datasets: [{
                    label: 'Score',
                    data: scores,
                    backgroundColor: scores.map(s => s >= 90 ? '#10b981' : s >= 70 ? '#f59e0b' : '#ef4444'),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true, max: 100,
                        ticks: { stepSize: 25, color: textColor, font: { size: 11 } },
                        grid: { color: gridColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 11 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
