@extends('layouts.app', ['title' => 'Dashboard'])

@php $chartPireps = auth()->user()->pireps()->latest()->limit(10)->get(['status', 'score', 'flight_number', 'created_at']); @endphp

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        @if(auth()->user()->status === 'suspended')
            <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 p-5">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-red-700 dark:text-red-400">Account Suspended</p>
                        @if(auth()->user()->suspension_reason)
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ auth()->user()->suspension_reason }}</p>
                        @endif
                        <p class="text-xs text-red-500 mt-2">Please contact staff to resolve this issue.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Welcome Header --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Here's your flight operations overview.</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <span class="stat-label">Total Hours</span>
                <span class="stat-value">{{ number_format(auth()->user()->total_hours, 1) }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Flights Logged</span>
                <span class="stat-value">{{ auth()->user()->total_flights }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Rank</span>
                <span class="stat-value">{{ auth()->user()->rank?->name ?? 'Candidate' }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Location</span>
                <span class="stat-value">{{ auth()->user()->last_location }}</span>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid lg:grid-cols-2 gap-4">
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">My PIREP Status</h3>
                <div class="relative h-64">
                    <canvas id="myPirepChart"></canvas>
                </div>
            </div>
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Recent Scores</h3>
                <div class="relative h-64">
                    <canvas id="myScoresChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Next Flight + Recent PIREPs --}}
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Next Flight</h2>
                    <span class="badge-info">Scheduled</span>
                </div>
                @php
                    $nextBid = auth()->user()->bids()->with('schedule')->latest()->first();
                @endphp
                @if($nextBid && $nextBid->schedule)
                    <div class="space-y-3">
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ substr($nextBid->schedule->departure, 0, 4) }}</p>
                                <p class="text-xs text-slate-500">{{ $nextBid->schedule->departure }}</p>
                            </div>
                            <div class="flex-1 flex items-center gap-2">
                                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                                <svg class="w-5 h-5 text-crimson-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                </svg>
                                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ substr($nextBid->schedule->arrival, 0, 4) }}</p>
                                <p class="text-xs text-slate-500">{{ $nextBid->schedule->arrival }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
                            <span>{{ $nextBid->schedule->flight_number }}</span>
                            <span>{{ $nextBid->schedule->flight_time }} hrs</span>
                        </div>
                        <a href="{{ route('simbrief') }}" wire:navigate class="btn-primary w-full mt-2">View Briefing</a>
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400 dark:text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        <p>No upcoming flights</p>
                        <a href="{{ route('flights') }}" wire:navigate class="btn-primary mt-4 inline-flex">Book a Flight</a>
                    </div>
                @endif
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recent PIREPs</h2>
                    <a href="{{ route('my-pireps') }}" wire:navigate class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline">View all</a>
                </div>
                @php
                    $recentPireps = auth()->user()->pireps()->latest()->take(5)->get();
                @endphp
                @if($recentPireps->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentPireps as $pirep)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $pirep->flight_number }}</p>
                                        <p class="text-xs text-slate-500">{{ $pirep->departure }} → {{ $pirep->arrival }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $pirep->flight_time }}h</p>
                                    <p class="text-xs text-slate-500">{{ $pirep->landing_rate }} fpm</p>
                                </div>
                                <div>
                                    @if($pirep->status === 'approved')
                                        <span class="badge-success">Approved</span>
                                    @elseif($pirep->status === 'pending')
                                        <span class="badge-warning">Pending</span>
                                    @else
                                        <span class="badge-danger">Rejected</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400 dark:text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <p>No PIREPs submitted yet</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Progression Section --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Progression</h2>
            @php
                $currentHours = auth()->user()->total_hours;
                $nextRank = \App\Models\Rank::where('minimum_hours', '>', $currentHours)->orderBy('minimum_hours')->first();
            @endphp
            @if($nextRank)
                @php
                    $currentRankMin = auth()->user()->rank?->minimum_hours ?? 0;
                    $progress = $nextRank->minimum_hours > $currentRankMin
                        ? min(100, (($currentHours - $currentRankMin) / ($nextRank->minimum_hours - $currentRankMin)) * 100)
                        : 0;
                @endphp
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->rank?->name ?? 'Candidate' }}</span>
                        <span class="text-slate-600 dark:text-slate-400">{{ $nextRank->name }}</span>
                    </div>
                    <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-crimson-500 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="text-xs text-slate-500">{{ number_format($nextRank->minimum_hours - $currentHours, 1) }} hours to next rank</p>
                    @if($nextRank->allowed_categories)
                        @php $unlocks = array_diff(explode(',', $nextRank->allowed_categories), explode(',', auth()->user()->rank?->allowed_categories ?? '')); @endphp
                        @if(count($unlocks) > 0)
                            <p class="text-xs text-crimson-600 dark:text-crimson-400 font-medium mt-1">Unlocks: {{ implode(', ', $unlocks) }}</p>
                        @endif
                    @endif
                </div>
            @else
                <p class="text-slate-500 dark:text-slate-400 text-sm">Maximum rank achieved!</p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    const pireps = @json($chartPireps);

    const statusCounts = { approved: 0, pending: 0, rejected: 0 };
    const scores = [];
    const scoreLabels = [];

    pireps.forEach(p => {
        statusCounts[p.status] = (statusCounts[p.status] || 0) + 1;
        scores.push(p.score || 0);
        scoreLabels.push(p.flight_number);
    });

    new Chart(document.getElementById('myPirepChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [statusCounts.approved || 0, statusCounts.pending || 0, statusCounts.rejected || 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } }
        }
    });

    if (scores.length) {
        new Chart(document.getElementById('myScoresChart'), {
            type: 'bar',
            data: {
                labels: scoreLabels,
                datasets: [{
                    label: 'Score',
                    data: scores,
                    backgroundColor: scores.map(s => s >= 80 ? '#10b981' : s >= 60 ? '#f59e0b' : '#ef4444'),
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { stepSize: 20 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
