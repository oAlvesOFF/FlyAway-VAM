<?php

use App\Models\User;
use App\Helpers\ActivityLogger;
use Livewire\Volt\Component;

?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pilots</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?php echo e(count($pilots)); ?> pilots</p>
        </div>
        <div class="flex items-center gap-2">
            <input wire:model.live.debounce="search" placeholder="Search pilots..." class="input-field text-sm px-3 py-1.5 w-48">
            <a href="<?php echo e(route('admin.export.pilots.csv')); ?>" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                CSV
            </a>
            <a href="<?php echo e(route('admin.export.pilots.print')); ?>" target="_blank" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Print / PDF
            </a>
            <button wire:click="filterPending" class="btn-secondary">
                <?php echo e($showPending ? 'Show All' : 'Show Pending Only'); ?>

            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-data="{ show: <?php if ((object) ('suspendingId') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('suspendingId'->value()); ?>')<?php echo e('suspendingId'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('suspendingId'); ?>')<?php endif; ?> }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="$wire.cancelSuspend()">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Suspend Pilot</h3>
            <p class="text-sm text-slate-500 mb-4">Provide a reason for suspension (optional). The pilot will see this on their dashboard.</p>
            <textarea wire:model="suspendReason" rows="3" class="input-field w-full" placeholder="e.g. Inactive for 90 days, violation of VA policy..."></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="$wire.cancelSuspend()" class="btn-secondary text-sm">Cancel</button>
                <button wire:click="suspend" class="btn-danger text-sm">Suspend Pilot</button>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">Pilot</th>
                        <th class="p-4 font-medium">Pilot ID</th>
                        <th class="p-4 font-medium">Rank</th>
                        <th class="p-4 font-medium">Hours</th>
                        <th class="p-4 font-medium">Flights</th>
                        <th class="p-4 font-medium">Status</th>
                        <th class="p-4 font-medium">API Key</th>
                        <th class="p-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pilots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pilot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-crimson-100 dark:bg-crimson-900/30 flex items-center justify-center">
                                        <span class="text-xs font-bold text-crimson-700 dark:text-crimson-400"><?php echo e(substr($pilot->name, 0, 2)); ?></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white"><?php echo e($pilot->name); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo e($pilot->email); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-500"><?php echo e($pilot->pilot_id ?? '—'); ?></td>
                            <td class="p-4">
                                <span class="badge-info"><?php echo e($pilot->rank?->name ?? 'Candidate'); ?></span>
                            </td>
                            <td class="p-4"><?php echo e(number_format($pilot->total_hours, 1)); ?></td>
                            <td class="p-4"><?php echo e($pilot->total_flights); ?></td>
                            <td class="p-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pilot->status === 'active'): ?>
                                    <span class="badge-success">Active</span>
                                <?php elseif($pilot->status === 'pending'): ?>
                                    <span class="badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge-danger">Suspended</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pilot->suspension_reason): ?>
                                    <div class="text-xs text-red-500 mt-1 max-w-40 truncate" title="<?php echo e($pilot->suspension_reason); ?>"><?php echo e($pilot->suspension_reason); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 font-mono text-xs text-slate-500">
                                    <span class="max-w-[80px] truncate"><?php echo e($pilot->api_key ? substr($pilot->api_key, 0, 7) . '...' : '—'); ?></span>
                                    <button wire:click="regenerateApiKey(<?php echo e($pilot->id); ?>)" class="text-crimson-600 hover:text-crimson-800 dark:text-crimson-400" title="Regenerate API Key">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-0.787M21 3v5.25m0 0h-4.992" /></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pilot->status === 'pending'): ?>
                                    <button wire:click="approve(<?php echo e($pilot->id); ?>)" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline mr-3">Approve</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pilot->status === 'active'): ?>
                                    <button wire:click="confirmSuspend(<?php echo e($pilot->id); ?>)" class="text-sm text-red-600 dark:text-red-400 hover:underline">Suspend</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pilot->status === 'suspended'): ?>
                                    <button wire:click="reactivate(<?php echo e($pilot->id); ?>)" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">Reactivate</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/admin/pilots.blade.php ENDPATH**/ ?>