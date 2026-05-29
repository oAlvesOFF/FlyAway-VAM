@extends('layouts.app', ['title' => 'Profile'])

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your account and view your pilot stats.</p>
        </div>

        {{-- Pilot Stats --}}
        @php $user = auth()->user(); @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card">
                <span class="stat-label">Pilot ID</span>
                <span class="stat-value">{{ $user->pilot_id }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Rank</span>
                <span class="stat-value">{{ $user->rank?->name ?? 'Candidate' }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Hours</span>
                <span class="stat-value">{{ number_format($user->total_hours, 1) }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Flights</span>
                <span class="stat-value">{{ $user->total_flights }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Status</span>
                <span class="stat-value text-{{ $user->status === 'active' ? 'emerald' : 'amber' }}-600 dark:text-{{ $user->status === 'active' ? 'emerald' : 'amber' }}-400">{{ ucfirst($user->status) }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Location</span>
                <span class="stat-value">{{ $user->last_location }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">SimBrief</span>
                <span class="stat-value text-sm">{{ $user->simbrief_username ?? 'Not set' }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Member Since</span>
                <span class="stat-value text-sm">{{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>

        {{-- Profile Form --}}
        <div class="card p-6">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="card p-6">
            <livewire:profile.update-password-form />
        </div>

        <div class="card p-6">
            <livewire:profile.delete-user-form />
        </div>
    </div>
@endsection
