<?php

use App\Models\ActiveFlight;
use App\Models\Aircraft;
use App\Models\Schedule;
use Livewire\Volt\Component;

?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">ACARS Client</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Browser-based flight tracking simulator. Send real-time position data to the Live Map.</p>
        </div>
        <a href="<?php echo e(route('live-map')); ?>" wire:navigate class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline">View Live Map →</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
        <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 p-4 text-red-700 dark:text-red-400 text-sm">
            <?php echo e($errorMessage); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-6">
        
        <div class="space-y-4">
            <div class="card p-5 space-y-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Flight Setup</h2>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Select Schedule</label>
                    <select wire:model.change="flight_number" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                        <option value="">— Select a flight —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($s['flight_number']); ?>"><?php echo e($s['flight_number']); ?> — <?php echo e($s['departure']); ?>→<?php echo e($s['arrival']); ?> (<?php echo e($s['aircraft_type']); ?>)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($flight_number): ?>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-xs text-slate-400">Route</span><p class="font-medium text-slate-900 dark:text-white"><?php echo e($departure); ?> → <?php echo e($arrival); ?></p></div>
                    <div><span class="text-xs text-slate-400">Aircraft</span><p class="font-medium text-slate-900 dark:text-white"><?php echo e($aircraft_registration); ?> (<?php echo e($aircraft_icao); ?>)</p></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$simulating): ?>
                    <button wire:click="startSimulation" class="btn-primary w-full text-sm py-2.5">
                        <svg class="w-4 h-4 inline mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Start Simulation
                    </button>
                <?php else: ?>
                    <button wire:click="advanceSimulation" class="btn-primary w-full text-sm py-2.5 mb-2">
                        Advance 2% Progress
                    </button>
                    <button wire:click="stopSimulation" class="w-full px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-1.5" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12"/></svg>
                        Stop Simulation
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="card p-5 space-y-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Live Telemetry</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Phase</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white capitalize"><?php echo e($phase); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Progress</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo e(round($progress * 100)); ?>%</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Altitude</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono"><?php echo e(number_format((int)$altitude)); ?> ft</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Ground Speed</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono"><?php echo e((int)$ground_speed); ?> kts</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Heading</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono"><?php echo e($heading); ?>°</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                        <p class="text-xs text-slate-400">Position</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white font-mono text-[11px]"><?php echo e($current_lat); ?>, <?php echo e($current_lng); ?></p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($simulating): ?>
                    <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-crimson-500 rounded-full transition-all duration-500" style="width: <?php echo e($progress * 100); ?>%"></div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="card p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">ACARS Log</h2>
                <button wire:click="clearLogs" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">Clear</button>
            </div>
            <div class="flex-1 bg-slate-950 dark:bg-black rounded-xl p-3 font-mono text-xs leading-relaxed max-h-[500px] overflow-y-auto" id="acars-log">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($logs) === 0): ?>
                    <span class="text-slate-600">// ACARS client ready. Select a flight and start simulation.</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="text-green-400"><?php echo e($log); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/acars.blade.php ENDPATH**/ ?>