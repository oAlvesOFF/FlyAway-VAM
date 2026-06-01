<?php

use App\Models\Airline;
use Livewire\Volt\Component;

?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Airlines</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage airlines within the system.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Airline</button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white"><?php echo e($editingId ? 'Edit Airline' : 'New Airline'); ?></h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label>
                    <input wire:model="name" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">ICAO *</label>
                    <input wire:model="icao" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm uppercase">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['icao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">IATA</label>
                    <input wire:model="iata" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm uppercase">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Callsign</label>
                    <input wire:model="callsign" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Country</label>
                    <input wire:model="country" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                </div>
                <div class="space-y-1 flex items-end">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="active" class="rounded border-slate-300 dark:border-slate-600">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="save" class="btn-primary text-sm px-4 py-2">Save</button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">Cancel</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="space-y-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="card-hover p-4 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-semibold text-slate-900 dark:text-white"><?php echo e($airline['name']); ?> (<?php echo e($airline['icao']); ?>)</h3>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($airline['active']): ?>
                            <span class="badge-success text-xs">Active</span>
                        <?php else: ?>
                            <span class="badge-error text-xs">Inactive</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500">Callsign: <?php echo e($airline['callsign'] ?: 'N/A'); ?> &middot; Country: <?php echo e($airline['country'] ?: 'N/A'); ?></p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit(<?php echo e($airline['id']); ?>)" class="text-xs text-crimson-600 hover:underline">Edit</button>
                    <button wire:click="delete(<?php echo e($airline['id']); ?>)" wire:confirm="Delete this airline?" class="text-xs text-red-500 hover:underline">Delete</button>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="card p-8 text-center text-slate-400">
                <p>No airlines found.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/admin/airlines/index.blade.php ENDPATH**/ ?>