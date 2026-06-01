<?php

use App\Models\Achievement;
use App\Models\Aircraft;
use App\Models\News;
use App\Models\Pirep;
use App\Models\Rank;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Notifications\PirepApproved;
use App\Notifications\PirepRejected;
use App\Helpers\ActivityLogger;
use App\Helpers\DiscordNotifier;
use Livewire\Volt\Component;
use Livewire\WithPagination;

?>

<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-7xl mx-auto space-y-6">
    
    <div class="flex flex-wrap gap-2">
        <button wire:click="clearCache" wire:confirm="Clear application cache?" class="btn-secondary text-xs">Clear Cache</button>
        <button wire:click="resetAllMaintenance" wire:confirm="Reset maintenance for ALL aircraft?" class="btn-secondary text-xs">Reset All Maint.</button>
        <a href="<?php echo e(route('admin.activity-log')); ?>" class="btn-secondary text-xs">Activity Log</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Admin Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your virtual airline.</p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat-card">
            <span class="stat-label">Pilots</span>
            <span class="stat-value"><?php echo e($totalPilots); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Aircraft</span>
            <span class="stat-value"><?php echo e($totalAircraft); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Schedules</span>
            <span class="stat-value"><?php echo e($totalSchedules); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending PIREPs</span>
            <span class="stat-value text-amber-600 dark:text-amber-400"><?php echo e($pendingPireps); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Pilots</span>
            <span class="stat-value text-crimson-600 dark:text-crimson-400"><?php echo e($pendingPilots); ?></span>
        </div>
    </div>

    
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">PIREP Status Breakdown</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="pirepChart"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Flights Last 7 Days</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Monthly Flight Hours (6 Months)</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="monthlyHoursChart"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Pilot Registrations</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="registrationsChart"></canvas>
            </div>
        </div>
    </div>

    
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Top 8 Routes</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="topRoutesChart"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Aircraft Category Distribution</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="aircraftCategoryChart"></canvas>
            </div>
        </div>
    </div>

    
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Fleet Utilization — Top 10 Aircraft</h3>
            <span class="text-xs text-slate-400">Approved PIREPs only</span>
        </div>
        <div style="position: relative; height: 220px; width: 100%;">
            <canvas id="fleetUtilChart"></canvas>
        </div>
    </div>

    
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="<?php echo e(route('admin.fleet')); ?>" wire:navigate class="card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-white">Fleet Manager</p>
                <p class="text-sm text-slate-500">Manage aircraft registrations</p>
            </div>
        </a>
        <a href="<?php echo e(route('admin.schedules')); ?>" wire:navigate class="card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.251 2.251 0 012.366 1.889m-5.8 0A2.251 2.251 0 0012.75 2.25H12a2.251 2.251 0 00-2.366 1.889"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-white">Schedules</p>
                <p class="text-sm text-slate-500">Manage flight schedules</p>
            </div>
        </a>
        <a href="<?php echo e(route('admin.news')); ?>" wire:navigate class="card-hover p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-crimson-100 dark:bg-crimson-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-crimson-600 dark:text-crimson-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-white">News</p>
                <p class="text-sm text-slate-500">Post announcements & updates</p>
            </div>
        </a>
    </div>

    
    <div x-data="{ show: <?php if ((object) ('rejectingId') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('rejectingId'->value()); ?>')<?php echo e('rejectingId'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('rejectingId'); ?>')<?php endif; ?> }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="$wire.cancelReject()">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Reject PIREP</h3>
            <p class="text-sm text-slate-500 mb-4">Provide a reason for rejection (optional). The pilot will see this in their notification.</p>
            <textarea wire:model="rejectReasonInput" rows="3" class="input-field w-full" placeholder="e.g. Incorrect aircraft registration, missing route data..."></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="$wire.cancelReject()" class="btn-secondary text-sm">Cancel</button>
                <button wire:click="rejectPirep" class="btn-danger text-sm">Reject PIREP</button>
            </div>
        </div>
    </div>

    
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">PIREPs</h2>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400"><?php echo e($recentPireps->total()); ?> total</span>
                <a href="<?php echo e(route('admin.export.pireps.csv')); ?>" class="text-xs text-crimson-600 dark:text-crimson-400 hover:underline font-medium">Export CSV</a>
            </div>
        </div>

        
        <div class="flex flex-wrap gap-3 mb-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
            <input wire:model.live.debounce="searchFlight" placeholder="Flight #" class="input-field text-xs px-3 py-1.5 w-28">
            <input wire:model.live.debounce="searchPilot" placeholder="Pilot" class="input-field text-xs px-3 py-1.5 w-32">
            <input wire:model.live.debounce="searchAircraft" placeholder="Aircraft" class="input-field text-xs px-3 py-1.5 w-28">
            <select wire:model.live="filterStatus" class="input-field text-xs px-3 py-1.5 w-28">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input wire:model.live="dateFrom" type="date" class="input-field text-xs px-3 py-1.5 w-36">
            <input wire:model.live="dateTo" type="date" class="input-field text-xs px-3 py-1.5 w-36">
            <button wire:click="$set('searchFlight', ''); $set('searchPilot', ''); $set('searchAircraft', ''); $set('filterStatus', ''); $set('dateFrom', ''); $set('dateTo', '')" class="text-xs text-slate-500 hover:text-crimson-600 dark:hover:text-crimson-400 font-medium">Clear</button>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedPireps) > 0): ?>
            <div class="flex items-center gap-3 mb-3 p-3 bg-crimson-50 dark:bg-crimson-950/30 rounded-xl">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?php echo e(count($selectedPireps)); ?> selected</span>
                <button wire:click="bulkApprove" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline font-medium">Approve All</button>
                <button wire:click="bulkReject" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">Reject All</button>
                <button wire:click="$set('selectedPireps', [])" class="text-sm text-slate-500 hover:underline">Clear</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentPireps->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <th class="pb-3 pr-2 w-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600">
                            </th>
                            <th class="pb-3 font-medium">Pilot</th>
                            <th class="pb-3 font-medium">Flight</th>
                            <th class="pb-3 font-medium">Route</th>
                            <th class="pb-3 font-medium">Time</th>
                            <th class="pb-3 font-medium">Score</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ detail: null }">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentPireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pirep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="border-b border-slate-100 dark:border-slate-800 <?php echo e(in_array($pirep->id, $selectedPireps) ? 'bg-crimson-50/50 dark:bg-crimson-950/20' : ''); ?>">
                                <td class="py-3 pr-2">
                                    <input type="checkbox" wire:model.live="selectedPireps" value="<?php echo e($pirep->id); ?>" class="rounded border-slate-300 dark:border-slate-600">
                                </td>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-3 text-slate-900 dark:text-white"><?php echo e($pirep->user?->name); ?></td>
                                <td class="py-3 font-medium"><?php echo e($pirep->flight_number); ?></td>
                                <td class="py-3 text-slate-500"><?php echo e($pirep->departure); ?> → <?php echo e($pirep->arrival); ?></td>
                                <td class="py-3 text-slate-500"><?php echo e($pirep->flight_time); ?>h</td>
                                <td class="py-3"><?php echo e($pirep->score); ?></td>
                                <td class="py-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pirep->status === 'approved'): ?>
                                        <span class="badge-success">Approved</span>
                                    <?php elseif($pirep->status === 'pending'): ?>
                                        <span class="badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge-danger">Rejected</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="py-3 text-right flex items-center justify-end gap-2">
                                    <button @click="detail = detail === <?php echo e($pirep->id); ?> ? null : <?php echo e($pirep->id); ?>" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline font-medium">View</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pirep->status === 'pending'): ?>
                                        <button wire:click="approvePirep(<?php echo e($pirep->id); ?>)" wire:loading.attr="disabled" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline font-medium">Approve</button>
                                        <button wire:click="confirmReject(<?php echo e($pirep->id); ?>)" wire:loading.attr="disabled" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">Reject</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                            <tr x-show="detail === <?php echo e($pirep->id); ?>" x-cloak>
                                <td colspan="7" class="py-4 px-4 bg-slate-50 dark:bg-slate-800/30 rounded-b-xl">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 text-sm">
                                        <div><span class="text-xs text-slate-400">Aircraft</span><p class="font-medium"><?php echo e($pirep->aircraft_registration); ?> (<?php echo e($pirep->aircraft_icao); ?>)</p></div>
                                        <div><span class="text-xs text-slate-400">Landing Rate</span><p class="font-medium"><?php echo e($pirep->landing_rate); ?> fpm</p></div>
                                        <div><span class="text-xs text-slate-400">Submitted</span><p class="font-medium"><?php echo e($pirep->submitted_at ? (\Carbon\Carbon::parse($pirep->submitted_at)->format('d M Y H:i')) : (\Carbon\Carbon::parse($pirep->created_at)->format('d M Y H:i'))); ?></p></div>
                                        <div><span class="text-xs text-slate-400">Aircraft Type</span><p class="font-medium"><?php echo e($pirep->aircraft_icao); ?></p></div>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pirep->route): ?>
                                        <div class="mb-2">
                                            <span class="text-xs text-slate-400">Route String</span>
                                            <p class="font-mono text-xs mt-0.5 text-slate-600 dark:text-slate-400"><?php echo e($pirep->route); ?></p>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pirep->log): ?>
                                        <div>
                                            <span class="text-xs text-slate-400">Flight Log</span>
                                            <pre class="mt-1 text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-900/50 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap max-h-48 overflow-y-auto"><?php echo e($pirep->log); ?></pre>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-slate-500 text-sm">No PIREPs yet.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentPireps->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($recentPireps->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    const pirepStatus = <?php echo json_encode($chartData['pirepStatus'], 15, 512) ?>;
    const weeklyFlights = <?php echo json_encode($chartData['weeklyFlights'], 15, 512) ?>;

    new Chart(document.getElementById('pirepChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [pirepStatus.approved || 0, pirepStatus.pending || 0, pirepStatus.rejected || 0],
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

    const dates = Object.keys(weeklyFlights);
    const counts = Object.values(weeklyFlights);

    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: dates.map(d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Flights',
                data: counts,
                backgroundColor: '#e11d48',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    const monthlyHours = <?php echo json_encode($chartData['monthlyHours'], 15, 512) ?>;
    new Chart(document.getElementById('monthlyHoursChart'), {
        type: 'line',
        data: {
            labels: Object.keys(monthlyHours),
            datasets: [{
                label: 'Hours',
                data: Object.values(monthlyHours).map(v => Number(v).toFixed(1)),
                borderColor: '#e11d48',
                backgroundColor: 'rgba(225, 29, 72, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: '#e11d48',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    const registrations = <?php echo json_encode($chartData['pilotRegistrations'], 15, 512) ?>;
    new Chart(document.getElementById('registrationsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(registrations),
            datasets: [{
                label: 'New Pilots',
                data: Object.values(registrations),
                backgroundColor: '#6366f1',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    const topRoutes = <?php echo json_encode($chartData['topRoutes'], 15, 512) ?>;
    const routeColors = ['#e11d48', '#f43f5e', '#fb7185', '#fda4af', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
    new Chart(document.getElementById('topRoutesChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(topRoutes),
            datasets: [{
                data: Object.values(topRoutes),
                backgroundColor: routeColors.slice(0, Object.keys(topRoutes).length),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } }
            }
        }
    });

    const acCategories = <?php echo json_encode($chartData['aircraftCategories'] ?? [], 15, 512) ?>;
    const aircraftCategories = Object.keys(acCategories);
    const categoryCounts = Object.values(acCategories);
    new Chart(document.getElementById('aircraftCategoryChart'), {
        type: 'bar',
        data: {
            labels: aircraftCategories,
            datasets: [{
                label: 'Aircraft',
                data: categoryCounts,
                backgroundColor: ['#e11d48', '#6366f1', '#10b981'],
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } },
                y: { grid: { display: false } }
            }
        }
    });

    const fleetUtil = <?php echo json_encode($chartData['fleetUtilization'], 15, 512) ?>;
    if (fleetUtil && fleetUtil.length > 0) {
        new Chart(document.getElementById('fleetUtilChart'), {
            type: 'bar',
            data: {
                labels: fleetUtil.map(f => f.aircraft_registration),
                datasets: [
                    {
                        label: 'Hours',
                        data: fleetUtil.map(f => Number(f.hours).toFixed(1)),
                        backgroundColor: '#e11d48',
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Flights',
                        data: fleetUtil.map(f => f.flights),
                        backgroundColor: '#6366f1',
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } } }
                },
                scales: {
                    y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Hours', font: { size: 11 } } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Flights', font: { size: 11 } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/admin/dashboard.blade.php ENDPATH**/ ?>