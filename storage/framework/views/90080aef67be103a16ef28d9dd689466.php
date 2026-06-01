<?php

use App\Models\Bid;
use App\Models\Schedule;
use App\Services\SimbriefService;
use Livewire\Volt\Component;

?>

<div class="max-w-7xl mx-auto space-y-6">

    
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-fill ph-airplane-tilt text-crimson-500"></i>
                SimBrief OFP
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Importe e visualize seu Plano de Voo Operacional.</p>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
    <div class="flex items-start gap-3 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 text-red-700 dark:text-red-400 text-sm font-medium">
        <i class="ph-fill ph-warning-circle text-lg mt-0.5"></i>
        <span><?php echo e($error); ?></span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-5 space-y-5">
        <div class="flex items-center gap-2 mb-1">
            <i class="ph-bold ph-gear text-slate-400"></i>
            <span class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Configuração SimBrief</span>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                    Pilot ID <span class="normal-case font-normal text-slate-500">(numérico)</span>
                </label>
                <input wire:model="simbriefId"
                       type="text"
                       inputmode="numeric"
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-crimson-500 focus:border-crimson-500 transition-colors"
                       placeholder="ex: 123456">
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Encontre em SimBrief → Account Settings</p>
            </div>
            
            
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
                    Username
                </label>
                <input wire:model="simbriefUsername"
                       type="text"
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-crimson-500 focus:border-crimson-500 transition-colors"
                       placeholder="seu_username_simbrief">
            </div>
            
            
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">Reserva</label>
                <select wire:model.live="selectedBidId" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-crimson-500 focus:border-crimson-500 transition-colors">
                    <option value="">— Selecione —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($b->id); ?>">
                            <?php echo e(optional($b->schedule)->flight_number ?? 'Deletada'); ?>

                            (<?php echo e(optional($b->schedule)->departure ?? '?'); ?>→<?php echo e(optional($b->schedule)->arrival ?? '?'); ?>)
                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            
            
            <div class="flex flex-col gap-2 justify-end mt-2 lg:mt-0">
                <button wire:click="saveCredentials"
                        class="w-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm font-semibold transition-colors flex items-center justify-center gap-1.5">
                    <i class="ph-bold ph-floppy-disk text-lg"></i>
                    Salvar Credenciais
                </button>
                <button wire:click="fetchOFP"
                        wire:loading.attr="disabled"
                        class="w-full bg-crimson-600 hover:bg-crimson-700 text-white rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-colors flex items-center justify-center gap-2">
                    <i wire:loading.remove wire:target="fetchOFP" class="ph-bold ph-download-simple text-lg"></i>
                    <svg wire:loading wire:target="fetchOFP" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="fetchOFP">Importar OFP</span>
                    <span wire:loading wire:target="fetchOFP">Buscando...</span>
                </button>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ofp): ?>
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">

        
        <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-black p-6 border-b border-slate-800">
            
            <div class="absolute inset-0 opacity-10 mix-blend-overlay" style="background-image: url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h40v40H0V0zm20 20h20v20H20V20zM0 20h20v20H0V20z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E\");"></div>

            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 z-10">
                
                <div class="flex items-center gap-4">
                    <div class="text-center">
                        <p class="text-4xl font-black text-white tracking-tight"><?php echo e($ofp['departure']); ?></p>
                        <p class="text-xs text-slate-400 mt-1 truncate max-w-[120px]"><?php echo e($ofp['departure_name'] ?? ''); ?></p>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">STD <?php echo e($ofp['departure_time'] ?? '--:--'); ?></p>
                    </div>
                    <div class="flex flex-col items-center gap-1 px-2">
                        <div class="flex items-center gap-2">
                            <div class="h-px w-8 sm:w-12 bg-crimson-500/50"></div>
                            <i class="ph-fill ph-airplane-in-flight text-2xl text-crimson-400"></i>
                            <div class="h-px w-8 sm:w-12 bg-crimson-500/50"></div>
                        </div>
                        <span class="text-xs text-slate-400 font-mono"><?php echo e($ofp['distance']); ?>nm</span>
                    </div>
                    <div class="text-center">
                        <p class="text-4xl font-black text-white tracking-tight"><?php echo e($ofp['arrival']); ?></p>
                        <p class="text-xs text-slate-400 mt-1 truncate max-w-[120px]"><?php echo e($ofp['arrival_name'] ?? ''); ?></p>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">STA <?php echo e($ofp['arrival_time'] ?? '--:--'); ?></p>
                    </div>
                </div>

                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-4 py-3 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Voo</p>
                        <p class="text-base font-bold text-white mt-1"><?php echo e($ofp['flight_number'] ?: '—'); ?></p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-4 py-3 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aeronave</p>
                        <p class="text-base font-bold text-white mt-1"><?php echo e($ofp['aircraft_icao']); ?></p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-4 py-3 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">FL Cruzeiro</p>
                        <p class="text-base font-bold text-white mt-1">FL<?php echo e($ofp['cruise_altitude'] ? ltrim($ofp['cruise_altitude'], 'FL') : '—'); ?></p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-4 py-3 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Block Time</p>
                        <p class="text-base font-bold text-white mt-1"><?php echo e($ofp['flight_time'] ?: '—'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 px-6 py-3 flex flex-wrap items-center gap-3">
            <a href="/file-pirep"
               class="bg-crimson-600 hover:bg-crimson-700 text-white border border-crimson-700 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors flex items-center gap-1.5 mr-2 shadow-sm">
                <i class="ph-bold ph-paper-plane-tilt text-sm"></i> File PIREP
            </a>

            <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mr-2 flex items-center gap-1.5">
                <i class="ph-bold ph-share-network text-lg"></i> Exportar:
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['prefile']['vatsim']) && is_array($ofp['prefile']['vatsim'])): ?>
                <a href="<?php echo e($ofp['prefile']['vatsim']['link'] ?? '#'); ?>" target="_blank" 
                   class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:text-crimson-600 dark:hover:text-crimson-400 hover:border-crimson-200 dark:hover:border-crimson-900 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors">
                    VATSIM
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['prefile']['ivao']) && is_array($ofp['prefile']['ivao'])): ?>
                <a href="<?php echo e($ofp['prefile']['ivao']['link'] ?? '#'); ?>" target="_blank" 
                   class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-900 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors">
                    IVAO
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['prefile']['poscon']) && is_array($ofp['prefile']['poscon'])): ?>
                <a href="<?php echo e($ofp['prefile']['poscon']['link'] ?? '#'); ?>" target="_blank" 
                   class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-200 dark:hover:border-emerald-900 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors">
                    POSCON
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <a href="https://www.simbrief.com/system/dispatch.php?shareuserid=<?php echo e($simbriefId); ?>" target="_blank" 
               class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors flex items-center gap-1.5">
                <i class="ph-bold ph-download-simple text-sm"></i> FMS Downloader
            </a>

            <div class="flex-1"></div>
            
            <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-full px-3 py-1 text-xs font-bold flex items-center gap-1.5">
                <i class="ph-bold ph-check-circle"></i> Rota Sincronizada
            </div>
        </div>

        
        <div class="flex gap-2 px-6 pt-4 border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                'route'   => ['label' => 'Rota',       'icon' => 'ph-map-trifold'],
                'weather' => ['label' => 'Tempo',      'icon' => 'ph-cloud-sun'],
                'fuel'    => ['label' => 'Combust.',   'icon' => 'ph-gas-pump'],
                'navlog'  => ['label' => 'NavLog',     'icon' => 'ph-list-numbers'],
                'charts'  => ['label' => 'Cartas',     'icon' => 'ph-images'],
                'tlr'     => ['label' => 'Desempenho', 'icon' => 'ph-trend-up'],
                'ofppdf'  => ['label' => 'OFP PDF',    'icon' => 'ph-file-pdf'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button wire:click="setTab('<?php echo e($key); ?>')"
                    class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold whitespace-nowrap transition-colors border-b-2 -mb-px rounded-t-lg
                           <?php echo e($activeTab === $key
                              ? 'border-crimson-500 text-crimson-600 dark:text-crimson-400 bg-crimson-50 dark:bg-crimson-900/10'
                              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50'); ?>">
                <i class="ph-bold <?php echo e($meta['icon']); ?> text-lg"></i>
                <?php echo e($meta['label']); ?>

            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <div class="p-6">

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'route'): ?>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Rota completa</p>
                    <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4 font-mono text-sm text-slate-800 dark:text-slate-200 leading-loose whitespace-pre-wrap break-all"><?php echo e($ofp['route_raw'] ?: '—'); ?></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ofp['image_url']): ?>
                <div>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Mapa da Rota</p>
                    <img src="<?php echo e($ofp['image_url']); ?>" alt="Mapa da Rota SimBrief" class="rounded-xl w-full border border-slate-200 dark:border-slate-700">
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'weather'): ?>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-bold text-crimson-600 dark:text-crimson-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <i class="ph-bold ph-airplane-takeoff text-lg"></i> <?php echo e($ofp['departure']); ?> — Partida
                    </p>
                    <p class="text-xs font-mono text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed"><?php echo e($ofp['weather_dep'] ?: 'N/D'); ?></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-bold text-crimson-600 dark:text-crimson-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <i class="ph-bold ph-airplane-landing text-lg"></i> <?php echo e($ofp['arrival']); ?> — Chegada
                    </p>
                    <p class="text-xs font-mono text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed"><?php echo e($ofp['weather_arr'] ?: 'N/D'); ?></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                        <i class="ph-bold ph-arrow-u-down-right text-lg"></i> <?php echo e($ofp['alternate'] ?: '—'); ?> — Alternado
                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ofp['alternate_name']): ?>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 ml-6"><?php echo e($ofp['alternate_name']); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="text-xs font-mono text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed"><?php echo e($ofp['weather_altn'] ?: 'N/D'); ?></p>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'fuel'): ?>
            <div class="space-y-4">
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                    Combustível <span class="normal-case font-medium text-slate-500">(<?php echo e(strtoupper($ofp['fuel_unit'] ?? 'lbs')); ?>)</span>
                </p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                        ['label' => 'Ramp',        'key' => 'fuel_ramp',         'color' => 'text-slate-900 dark:text-white'],
                        ['label' => 'Block',       'key' => 'fuel_block',        'color' => 'text-slate-900 dark:text-white'],
                        ['label' => 'Trip',        'key' => 'fuel_trip',         'color' => 'text-emerald-600 dark:text-emerald-400'],
                        ['label' => 'Pouso',       'key' => 'fuel_plan_landing', 'color' => 'text-emerald-600 dark:text-emerald-400'],
                        ['label' => 'Contingência','key' => 'fuel_contingency',  'color' => 'text-amber-600 dark:text-amber-400'],
                        ['label' => 'Alternado',   'key' => 'fuel_alternate',    'color' => 'text-amber-600 dark:text-amber-400'],
                        ['label' => 'Reserva',     'key' => 'fuel_reserve',      'color' => 'text-purple-600 dark:text-purple-400'],
                        ['label' => 'Extra',       'key' => 'fuel_extra',        'color' => 'text-purple-600 dark:text-purple-400'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $val = $ofp[$f['key']] ?? ''; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($val): ?>
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 text-center border border-slate-200 dark:border-slate-700">
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest"><?php echo e($f['label']); ?></p>
                        <p class="text-xl font-black mt-1 <?php echo e($f['color']); ?>"><?php echo e(number_format((float)$val)); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'navlog'): ?>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                        <tr class="text-xs text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 font-bold uppercase tracking-widest">#</th>
                            <th class="py-3 px-4 font-bold uppercase tracking-widest">Ident</th>
                            <th class="py-3 px-4 font-bold uppercase tracking-widest">Via</th>
                            <th class="py-3 px-4 font-bold uppercase tracking-widest">Nome</th>
                            <th class="py-3 px-4 font-bold uppercase tracking-widest">Altitude</th>
                            <th class="py-3 px-4 font-bold uppercase tracking-widest">Dist.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ofp['waypoints']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $wp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $wpIdent = is_array($wp['ident']    ?? null) ? json_encode($wp['ident'])    : ($wp['ident']    ?? '-');
                            $wpName  = is_array($wp['name']     ?? null) ? json_encode($wp['name'])     : ($wp['name']     ?? '');
                            $wpAlt   = is_array($wp['altitude'] ?? null) ? json_encode($wp['altitude']) : ($wp['altitude'] ?? '');
                            $wpDist  = is_array($wp['distance'] ?? null) ? json_encode($wp['distance']) : ($wp['distance'] ?? '');
                            $wpAwy   = is_array($wp['airway']   ?? null) ? json_encode($wp['airway'])   : ($wp['airway']   ?? '');
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-2.5 px-4 text-xs text-slate-400 dark:text-slate-500"><?php echo e($i + 1); ?></td>
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-900 dark:text-white"><?php echo e($wpIdent); ?></td>
                            <td class="py-2.5 px-4 font-mono text-xs text-slate-500 dark:text-slate-400"><?php echo e($wpAwy ?: '—'); ?></td>
                            <td class="py-2.5 px-4 text-slate-600 dark:text-slate-300 text-xs"><?php echo e($wpName); ?></td>
                            <td class="py-2.5 px-4 text-slate-600 dark:text-slate-300 font-mono text-xs"><?php echo e($wpAlt ? $wpAlt.'ft' : '—'); ?></td>
                            <td class="py-2.5 px-4 text-slate-600 dark:text-slate-300 font-mono text-xs"><?php echo e($wpDist ? $wpDist.'nm' : '—'); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($ofp['waypoints'])): ?>
                <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-sm font-medium">Nenhum waypoint disponível.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'charts'): ?>
            <div class="space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['chart_images'])): ?>
                    <div class="grid md:grid-cols-2 gap-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ofp['chart_images']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                            <img src="<?php echo e($img['url']); ?>" alt="<?php echo e($img['name']); ?>" class="w-full h-auto object-cover">
                            <div class="p-3 text-center bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo e($img['name']); ?></p>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="py-16 text-center space-y-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
                        <i class="ph-fill ph-images text-5xl text-slate-300 dark:text-slate-600"></i>
                        <p class="text-slate-700 dark:text-slate-300 font-bold">Nenhuma Carta Disponível</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'tlr'): ?>
            <div class="space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['tlr']['takeoff']) || !empty($ofp['tlr']['landing'])): ?>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['tlr']['takeoff'])): ?>
                        <div>
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Takeoff Performance</p>
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4 font-mono text-[10px] sm:text-xs text-slate-800 dark:text-slate-200 leading-loose whitespace-pre-wrap break-all"><?php echo e($ofp['tlr']['takeoff']); ?></div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ofp['tlr']['landing'])): ?>
                        <div>
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Landing Performance</p>
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4 font-mono text-[10px] sm:text-xs text-slate-800 dark:text-slate-200 leading-loose whitespace-pre-wrap break-all"><?php echo e($ofp['tlr']['landing']); ?></div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="py-16 text-center space-y-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
                        <i class="ph-fill ph-trend-up text-5xl text-slate-300 dark:text-slate-600"></i>
                        <p class="text-slate-700 dark:text-slate-300 font-bold">Nenhum Relatório de Desempenho (TLR)</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'ofppdf'): ?>
            <?php
                $rawPdf = $ofp['pdf_link'] ?? $ofp['pdf_url'] ?? '';
                $proxyPdf = $rawPdf ? route('simbrief.ofp-pdf', ['url' => $rawPdf]) : '';
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proxyPdf): ?>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Operational Flight Plan</p>
                    <a href="<?php echo e($rawPdf); ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-crimson-600 dark:text-crimson-400 hover:text-crimson-700 dark:hover:text-crimson-300 hover:underline">
                        Abrir em nova aba <i class="ph-bold ph-arrow-square-out text-sm"></i>
                    </a>
                </div>
                
                <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 h-[780px]">
                    <iframe
                        src="<?php echo e($proxyPdf); ?>"
                        class="absolute inset-0 w-full h-full border-none"
                        title="OFP PDF"
                        allowfullscreen>
                        <p class="p-6 text-sm text-slate-500 dark:text-slate-400">
                            O seu navegador não suporta PDF inline.
                            <a href="<?php echo e($rawPdf); ?>" target="_blank" class="text-crimson-600 dark:text-crimson-400 font-bold hover:underline">Clique aqui para abrir.</a>
                        </p>
                    </iframe>
                </div>
            </div>
            <?php else: ?>
            <div class="py-16 text-center space-y-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
                <i class="ph-fill ph-file-dashed text-5xl text-slate-300 dark:text-slate-600"></i>
                <p class="text-slate-700 dark:text-slate-300 font-bold">Nenhum PDF disponível</p>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Este plano de voo não inclui um documento PDF gerado.</p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    </div>

    <?php else: ?>
    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-12 text-center space-y-5">
        <div class="mx-auto w-20 h-20 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700/50 flex items-center justify-center">
            <i class="ph-fill ph-airplane-in-flight text-4xl text-slate-300 dark:text-slate-600"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Nenhum OFP carregado</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">Insira seu Pilot ID do SimBrief, selecione uma reserva e clique em <strong class="text-slate-700 dark:text-slate-300">Importar OFP</strong>.</p>
        </div>
        <div class="inline-flex items-center gap-2 text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-full px-4 py-2">
            <i class="ph-fill ph-info text-base"></i>
            O plano de voo deve estar ativo no SimBrief antes de importar.
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/simbrief.blade.php ENDPATH**/ ?>