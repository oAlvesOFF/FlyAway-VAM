@extends('layouts.app', ['title' => 'Profile'])

@section('content')

@push('styles')
<style>
/* ══ Profile Settings Theme ══════════════════════════════════ */
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
    --bg-input: #ffffff;
    --border-input: #ced4da;
    --shadow-card: 0 2px 8px rgba(0, 0, 0, .04);
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
    --bg-input: #23263a;
    --border-input: #3a3f54;
    --shadow-card: none;
}

body, main { background: var(--bg-page) !important; }

.pf-wrap {
    padding: 32px; max-width: 1100px; margin: 0 auto;
    font-family: 'Inter', sans-serif;
}

.pf-header {
    margin-bottom: 24px;
}
.pf-title { font-size: 24px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
.pf-title i { color: var(--text-badge); }
.pf-subtitle { font-size: 14px; color: var(--text-muted); }

/* Stats Grid */
.pf-stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px; margin-bottom: 32px;
}
.pf-stat-card {
    background: var(--bg-card); border: 1px solid var(--border-card);
    border-radius: 10px; padding: 16px; box-shadow: var(--shadow-card);
}
.pf-stat-lbl { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
.pf-stat-val { font-size: 18px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px; }
.pf-stat-val.green { color: #10b981; }

/* Sections */
.pf-section {
    background: var(--bg-card); border: 1px solid var(--border-card);
    border-radius: 12px; padding: 24px; margin-bottom: 24px;
    box-shadow: var(--shadow-card);
}
.pf-section-header { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-card); }
.pf-section-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.pf-section-desc { font-size: 13px; color: var(--text-muted); }

/* Forms */
.pf-form-group { margin-bottom: 16px; }
.pf-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px; }
.pf-input {
    width: 100%; background: var(--bg-input); border: 1px solid var(--border-input);
    color: var(--text-primary); padding: 10px 14px; border-radius: 6px; font-size: 14px;
    outline: none; transition: border-color .2s;
}
.pf-input:focus { border-color: var(--text-badge); }
.pf-error { color: #ef4444; font-size: 12px; margin-top: 4px; font-weight: 600; }
.pf-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    background: var(--text-badge); color: #fff; padding: 10px 20px; border-radius: 6px;
    font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: opacity .2s;
}
.pf-btn:hover { opacity: 0.9; }
.pf-btn-danger { background: #ef4444; }
.pf-btn-secondary { background: var(--bg-header); color: var(--text-primary); border: 1px solid var(--border-card); }

/* Avatar specific fix */
.pf-avatar-wrap { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.pf-avatar-img {
    width: 80px !important; height: 80px !important; border-radius: 50% !important;
    object-fit: cover !important; border: 3px solid var(--border-card) !important;
    display: block !important; flex-shrink: 0 !important;
}
.pf-avatar-placeholder {
    width: 80px; height: 80px; border-radius: 50%; background: var(--text-badge); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800;
    border: 3px solid var(--border-card); flex-shrink: 0;
}
</style>
@endpush

<div class="pf-wrap">
    <div class="pf-header">
        <div class="pf-title"><i class="ph-fill ph-user-circle-gear"></i> Profile Settings</div>
        <div class="pf-subtitle">Manage your account information, pilot statistics, and security settings.</div>
    </div>

    {{-- Pilot Stats Grid --}}
    @php $user = auth()->user(); @endphp
    <div class="pf-stats-grid">
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Pilot ID</div>
            <div class="pf-stat-val"><i class="ph-fill ph-identification-badge" style="color: var(--text-badge); font-size: 20px;"></i> {{ $user->pilot_id }}</div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Rank</div>
            <div class="pf-stat-val"><i class="ph-fill ph-medal" style="color: #f59e0b; font-size: 20px;"></i> {{ $user->rank?->name ?? 'Candidate' }}</div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Total Hours</div>
            <div class="pf-stat-val"><i class="ph-fill ph-clock" style="color: var(--text-badge); font-size: 20px;"></i> {{ number_format($user->total_hours, 1) }}</div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Flights</div>
            <div class="pf-stat-val"><i class="ph-fill ph-airplane-tilt" style="color: var(--text-badge); font-size: 20px;"></i> {{ $user->total_flights }}</div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Status</div>
            <div class="pf-stat-val {{ $user->status === 'active' ? 'green' : '' }}">
                <i class="ph-fill {{ $user->status === 'active' ? 'ph-check-circle' : 'ph-warning-circle' }}" style="font-size: 20px;"></i>
                {{ ucfirst($user->status) }}
            </div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Location</div>
            <div class="pf-stat-val" style="font-family: monospace;">
                <i class="ph-fill ph-map-pin" style="color: #ef4444; font-size: 20px;"></i> {{ $user->last_location ?? '---' }}
            </div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">SimBrief</div>
            <div class="pf-stat-val" style="font-size: 15px;">
                <i class="ph-fill ph-paper-plane-tilt" style="color: var(--text-badge); font-size: 18px;"></i> {{ $user->simbrief_username ?? 'Not set' }}
            </div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-lbl">Member Since</div>
            <div class="pf-stat-val" style="font-size: 15px;">
                <i class="ph-fill ph-calendar" style="color: var(--text-muted); font-size: 18px;"></i> {{ $user->created_at->format('M Y') }}
            </div>
        </div>
    </div>

    {{-- Forms --}}
    <div class="pf-section">
        <livewire:profile.update-profile-information-form />
    </div>

    <div class="pf-section">
        <livewire:profile.update-password-form />
    </div>

    <div class="pf-section" style="border-color: rgba(239, 68, 68, 0.3);">
        <livewire:profile.delete-user-form />
    </div>
</div>
@endsection
