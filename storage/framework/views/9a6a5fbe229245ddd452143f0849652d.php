<?php

use App\Models\Achievement;
use App\Models\Pirep;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Statistics</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detailed breakdown of your flying career.</p>
    </div>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <span class="stat-label">Total Flights</span>
            <span class="stat-value"><?php echo e($totalFlights); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Hours</span>
            <span class="stat-value"><?php echo e(number_format($totalHours, 1)); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Average Score</span>
            <span class="stat-value"><?php echo e(number_format($avgScore, 0)); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Achievements</span>
            <span class="stat-value"><?php echo e($unlockedAchievements); ?>/<?php echo e($totalAchievements); ?></span>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Best Landing</p>
            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1"><?php echo e($bestLanding ?? '—'); ?> fpm</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Worst Landing</p>
            <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1"><?php echo e($worstLanding ?? '—'); ?> fpm</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Favorite Aircraft</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1 truncate"><?php echo e($mostFlownAircraft); ?></p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Favorite Route</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1 truncate"><?php echo e($favoriteRoute); ?></p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Monthly Flight Hours (12mo)</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="monthlyHoursChart"></canvas>
            </div>
        </div>

        
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Score Trend (Last 20)</h3>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="scoreTrendChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Aircraft Usage</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($aircraftBreakdown) > 0): ?>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $aircraftBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ac): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-slate-700 dark:text-slate-300"><?php echo e($ac['aircraft_registration']); ?></span>
                        <span class="text-xs text-slate-400">(<?php echo e($ac['aircraft_icao']); ?>)</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span><?php echo e($ac['flights']); ?> flights</span>
                        <span><?php echo e(number_format($ac['hours'], 1)); ?>h</span>
                    </div>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-crimson-500 h-1.5 rounded-full" style="width: <?php echo e(($ac['hours'] / max(array_column($aircraftBreakdown, 'hours'))) * 100); ?>%"></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php else: ?>
                <p class="text-sm text-slate-400">No data yet.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Top Routes</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($topRoutes) > 0): ?>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $topRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-center justify-between text-sm">
                    <span class="font-mono text-slate-700 dark:text-slate-300"><?php echo e($route); ?></span>
                    <span class="text-slate-500"><?php echo e($count); ?> flights</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?php echo e(($count / max($topRoutes)) * 100); ?>%"></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php else: ?>
                <p class="text-sm text-slate-400">No data yet.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    // Monthly Hours Chart
    const mh = <?php echo json_encode($monthlyHours, 15, 512) ?>;
    if (Object.keys(mh).length > 0) {
        new Chart(document.getElementById('monthlyHoursChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(mh),
                datasets: [{
                    label: 'Hours',
                    data: Object.values(mh).map(v => Number(v).toFixed(1)),
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
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Score Trend
    const scores = <?php echo json_encode($scoreTrend, 15, 512) ?>;
    if (scores.length > 0) {
        new Chart(document.getElementById('scoreTrendChart'), {
            type: 'line',
            data: {
                labels: scores.map(s => s.flight_number),
                datasets: [{
                    label: 'Score',
                    data: scores.map(s => s.score),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#6366f1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, min: 0, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/pilot-stats.blade.php ENDPATH**/ ?>