<?php

use App\Models\ActivityLog;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $filter = '';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = ActivityLog::with('user')->latest();

        if ($this->filter) {
            $query->where('action', $this->filter);
        }

        return ['logs' => $query->paginate(50)];
    }
}; ?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Activity Log</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Staff and admin action history.</p>
        </div>
        <select wire:model.live="filter" class="input-field text-sm px-3 py-1.5 w-44">
            <option value="">All Actions</option>
            <option value="approve_pirep">Approve PIREP</option>
            <option value="reject_pirep">Reject PIREP</option>
            <option value="bulk_approve_pirep">Bulk Approve</option>
            <option value="approve_pilot">Approve Pilot</option>
            <option value="suspend_pilot">Suspend Pilot</option>
            <option value="reactivate_pilot">Reactivate Pilot</option>
        </select>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">Date/Time</th>
                        <th class="p-4 font-medium">Staff</th>
                        <th class="p-4 font-medium">Action</th>
                        <th class="p-4 font-medium">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="p-4 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="p-4">
                                @if($log->user)
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $log->user->name }}</span>
                                    <span class="text-xs text-slate-400 ml-1">{{ $log->user->pilot_id }}</span>
                                @else
                                    <span class="text-slate-400">System</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @php
                                    $badge = match($log->action) {
                                        'approve_pirep', 'approve_pilot', 'reactivate_pilot', 'bulk_approve_pirep' => 'badge-success',
                                        'reject_pirep', 'suspend_pilot' => 'badge-danger',
                                        default => 'badge-info',
                                    };
                                @endphp
                                <span class="{{ $badge }} whitespace-nowrap">{{ str_replace('_', ' ', ucwords($log->action, '_')) }}</span>
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 max-w-md truncate">{{ $log->description ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">No activity logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div>{{ $logs->links() }}</div>
    @endif
</div>
