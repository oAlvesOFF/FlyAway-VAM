<?php

use App\Models\Schedule;
use Livewire\Volt\Component;

?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Schedules</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?php echo e(count($schedules)); ?> flights scheduled</p>
        </div>
        <button wire:click="$set('showForm', true)" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Schedule
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4"><?php echo e($editingId ? 'Edit Schedule' : 'Add Schedule'); ?></h3>
            <form wire:submit="save" class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Number</label>
                    <input wire:model="flight_number" class="input-field" placeholder="VA001">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Departure (ICAO)</label>
                    <input wire:model="departure" class="input-field" placeholder="YSSY" maxlength="4" style="text-transform: uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Arrival (ICAO)</label>
                    <input wire:model="arrival" class="input-field" placeholder="YMML" maxlength="4" style="text-transform: uppercase">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Route</label>
                    <input wire:model="route" class="input-field" placeholder="YSSY DCT WOL DCT YMML">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Aircraft Type</label>
                    <input wire:model="aircraft_type" class="input-field" placeholder="B738">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Flight Time (hrs)</label>
                    <input wire:model="flight_time" class="input-field" type="number" step="0.1" placeholder="2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Altitude</label>
                    <input wire:model="altitude" class="input-field" type="number" placeholder="30000">
                </div>
                <div class="md:col-span-3 flex gap-3">
                    <button type="submit" class="btn-primary"><?php echo e($editingId ? 'Update' : 'Create'); ?></button>
                    <button type="button" wire:click="resetForm" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">Flight</th>
                        <th class="p-4 font-medium">Route</th>
                        <th class="p-4 font-medium">Type</th>
                        <th class="p-4 font-medium">Time</th>
                        <th class="p-4 font-medium">Departure</th>
                        <th class="p-4 font-medium">Altitude</th>
                        <th class="p-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="p-4 font-medium text-slate-900 dark:text-white"><?php echo e($s->flight_number); ?></td>
                            <td class="p-4"><?php echo e($s->departure); ?> → <?php echo e($s->arrival); ?></td>
                            <td class="p-4"><span class="badge-info"><?php echo e($s->aircraft_type); ?></span></td>
                            <td class="p-4"><?php echo e($s->flight_time); ?>h</td>
                            <td class="p-4 text-slate-500"><?php echo e($s->departure_time); ?></td>
                            <td class="p-4 text-slate-500"><?php echo e(number_format($s->altitude)); ?>ft</td>
                            <td class="p-4 text-right">
                                <button wire:click="edit(<?php echo e($s->id); ?>)" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline mr-3">Edit</button>
                                <button wire:click="delete(<?php echo e($s->id); ?>)" wire:confirm="Delete this schedule?" class="text-sm text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/admin/schedules.blade.php ENDPATH**/ ?>