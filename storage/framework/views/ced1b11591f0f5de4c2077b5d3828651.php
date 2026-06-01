<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FlyAway Virtual | Premium Virtual Aviation</title>
    <meta name="description" content="<?php echo e(number_format($scheduleCount)); ?>+ real-world routes. Rank progression, ACARS tracking, and a platform unlike anything else in virtual aviation.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <script>
        // Premium Theme Switcher
        function updateThemeClass() {
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        updateThemeClass();
    </script>

    <style>
        /* Smooth transitions */
        html { transition: background-color 0.4s ease, color 0.4s ease; }
        
        /* Premium Leaflet Styling overrides */
        .leaflet-container { background: transparent !important; }
        
        .dark .leaflet-popup-content-wrapper, .dark .leaflet-popup-tip {
            background: rgba(20, 20, 20, 0.85) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6) !important;
            border-radius: 12px !important;
        }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            color: #111 !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
            border-radius: 12px !important;
        }
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
        
        /* Floating animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    </style>
</head>
<body class="bg-[#fafafa] text-zinc-900 dark:bg-[#050505] dark:text-zinc-50 font-sans antialiased overflow-x-hidden selection:bg-red-500/30 selection:text-red-900 dark:selection:text-red-100">


<div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden">
    <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] rounded-full bg-red-600/5 dark:bg-red-600/10 blur-[120px] mix-blend-multiply dark:mix-blend-screen opacity-70 animate-pulse" style="animation-duration: 8s;"></div>
    <div class="absolute top-[20%] -right-[10%] w-[50vw] h-[50vw] rounded-full bg-blue-600/5 dark:bg-blue-600/10 blur-[100px] mix-blend-multiply dark:mix-blend-screen opacity-50 animate-pulse" style="animation-duration: 12s;"></div>
</div>


<nav class="fixed top-0 inset-x-0 z-[100] transition-all duration-300 bg-white/70 dark:bg-[#050505]/70 backdrop-blur-2xl border-b border-black/5 dark:border-white/[0.05]">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        
        
        <a href="/" class="flex items-center gap-3 group">
            <img src="https://iili.io/C32u0Ff.png" alt="FlyAway Logo" class="w-8 h-8 rounded-xl shadow-lg shadow-red-600/20 group-hover:scale-105 transition-transform duration-300 object-cover">
            <span class="text-lg font-extrabold tracking-tight text-zinc-900 dark:text-white">FLY<span class="text-red-600">AWAY</span></span>
        </a>

        
        <div class="hidden md:flex items-center gap-8">
            <a href="/" class="text-[13px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Home</a>
            <a href="#features" class="text-[13px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Features</a>
            <a href="#live-map" class="text-[13px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Live Map</a>
            <a href="#schedules" class="text-[13px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Schedules</a>
        </div>

        
        <div class="flex items-center gap-4">
            <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-zinc-500 dark:text-zinc-400 transition-colors focus:outline-none" aria-label="Toggle Theme">
                <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg class="w-[18px] h-[18px] block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
            <div class="w-px h-4 bg-zinc-200 dark:bg-white/10 hidden sm:block"></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="hidden sm:block text-[13px] font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors">Dashboard</a>
                <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-zinc-900 dark:bg-white text-white dark:text-black text-[13px] font-bold hover:scale-105 transition-transform shadow-lg shadow-black/10 dark:shadow-white/10">Go to Cockpit &rarr;</a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="hidden sm:block text-[13px] font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors">Log in</a>
                <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-zinc-900 dark:bg-white text-white dark:text-black text-[13px] font-bold hover:scale-105 transition-transform shadow-lg shadow-black/10 dark:shadow-white/10">Sign up</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</nav>


<section class="pt-40 pb-24 relative z-10 flex flex-col items-center justify-center min-h-[90vh]">
    <div class="max-w-[1000px] mx-auto px-6 text-center">
        
        
        <div class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-white/60 dark:bg-white/5 border border-black/5 dark:border-white/10 backdrop-blur-md mb-8 shadow-sm">
            <div class="flex items-center justify-center">
                <span class="absolute w-2 h-2 rounded-full bg-green-500 animate-ping opacity-75"></span>
                <span class="relative w-2 h-2 rounded-full bg-green-500"></span>
            </div>
            <span class="text-[12px] font-semibold text-zinc-800 dark:text-zinc-300 tracking-wide"><?php echo e(count($mappedFlights)); ?> Pilots Online Right Now</span>
            <span class="text-zinc-300 dark:text-zinc-600 mx-1">|</span>
            <a href="#live-map" class="text-[12px] font-bold text-red-600 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors flex items-center gap-1">Live Map <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>

        
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tighter text-zinc-900 dark:text-white leading-[1.05] mb-6">
            The Virtual Airline built<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-400">for pilots who want more.</span>
        </h1>

        
        <p class="text-lg md:text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed mb-10 font-medium">
            Over <strong class="text-zinc-900 dark:text-white"><?php echo e(number_format($scheduleCount)); ?></strong> real-world routes, advanced ACARS tracking, and an immersive career progression platform.
        </p>

        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('flights')); ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-red-600 hover:bg-red-700 text-white font-bold text-sm transition-all hover:shadow-[0_0_20px_rgba(220,38,38,0.4)] hover:-translate-y-1">Find a Flight</a>
            <?php else: ?>
                <a href="<?php echo e(route('register')); ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-red-600 hover:bg-red-700 text-white font-bold text-sm transition-all hover:shadow-[0_0_20px_rgba(220,38,38,0.4)] hover:-translate-y-1">Create free account</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-white dark:bg-white/5 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white font-bold text-sm backdrop-blur-md transition-all hover:bg-zinc-50 dark:hover:bg-white/10 hover:-translate-y-1">Explore features</a>
        </div>
    </div>

    
    <div class="w-full max-w-5xl mx-auto px-6 perspective-1000 animate-float-delayed">
        <div class="relative bg-white/50 dark:bg-black/50 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-2xl overflow-hidden shadow-2xl shadow-black/10 dark:shadow-white/5 transform rotate-x-12 hover:rotate-x-0 transition-transform duration-700 ease-out">
            
            
            <div class="h-12 bg-white/40 dark:bg-white/5 border-b border-black/5 dark:border-white/5 flex items-center px-4 gap-4">
                <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <div class="flex-1 max-w-md mx-auto bg-white/60 dark:bg-black/40 border border-black/5 dark:border-white/5 rounded-md h-7 flex items-center justify-center text-[11px] font-mono text-zinc-500 dark:text-zinc-400">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    app.flyaway.virtual
                </div>
            </div>

            
            <div class="flex h-[400px] bg-white/80 dark:bg-zinc-950/80">
                
                <div class="w-48 border-r border-black/5 dark:border-white/5 p-4 flex flex-col gap-1">
                    <div class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-2 px-3">Menu</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Dashboard','Bookings','Live Map','Fleet']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="px-3 py-2 rounded-lg text-xs font-semibold <?php echo e($i===0 ? 'bg-red-500/10 text-red-600 dark:text-red-500' : 'text-zinc-600 dark:text-zinc-400'); ?> flex items-center gap-3">
                            <div class="w-4 h-4 rounded <?php echo e($i===0 ? 'bg-red-500' : 'bg-zinc-300 dark:bg-zinc-800'); ?>"></div>
                            <?php echo e($item); ?>

                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                
                <div class="flex-1 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="h-6 w-48 bg-zinc-200 dark:bg-zinc-800 rounded-md"></div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-500 to-red-800"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="h-24 bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-xl shadow-sm p-4 flex flex-col justify-between">
                            <div class="h-3 w-16 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                            <div class="h-8 w-24 bg-red-100 dark:bg-red-500/20 rounded"></div>
                        </div>
                        <div class="h-24 bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-xl shadow-sm p-4 flex flex-col justify-between">
                            <div class="h-3 w-16 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                            <div class="h-8 w-24 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                        </div>
                        <div class="h-24 bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-xl shadow-sm p-4 flex flex-col justify-between">
                            <div class="h-3 w-16 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                            <div class="h-8 w-24 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                        </div>
                    </div>
                    <div class="h-40 bg-zinc-100 dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-xl"></div>
                </div>
            </div>
        </div>
    </div>
</section>


<section id="features" class="py-32 relative z-10">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="text-center max-w-2xl mx-auto mb-20">
            <h2 class="text-red-600 dark:text-red-500 font-bold tracking-widest text-xs uppercase mb-3">Engineered for Excellence</h2>
            <h3 class="text-4xl md:text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-white mb-6">More than just logging flights.</h3>
            <p class="text-lg text-zinc-600 dark:text-zinc-400 font-medium">FlyAway combines real airline data with deep gamification, offering an environment that rewards precision and dedication.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-3xl p-8 hover:-translate-y-2 transition-transform duration-300 shadow-xl shadow-black/5">
                <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-500/10 text-red-600 flex items-center justify-center mb-6">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <h4 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">Global Flight Network</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">Access over <strong class="text-zinc-900 dark:text-zinc-200"><?php echo e(number_format($scheduleCount)); ?></strong> real-world schedules. From short regional hops to ultra-long-haul intercontinental routes, fly the fleet you love.</p>
            </div>

            
            <div class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-3xl p-8 hover:-translate-y-2 transition-transform duration-300 shadow-xl shadow-black/5">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/10 text-amber-600 flex items-center justify-center mb-6">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <h4 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">Dynamic Challenges</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">Daily and weekly objectives keep things fresh. Complete challenges to earn exclusive pilot awards and bonus virtual currency.</p>
            </div>

            
            <div class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-3xl p-8 hover:-translate-y-2 transition-transform duration-300 shadow-xl shadow-black/5">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-500/10 text-blue-600 flex items-center justify-center mb-6">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <h4 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">Smart ACARS Tracking</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">Our custom ACARS client automatically tracks your flight telemetry, grading your landings, phase management, and overall flight quality.</p>
            </div>
        </div>
    </div>
</section>


<section id="live-map" class="py-24 relative z-10 border-y border-black/5 dark:border-white/5 bg-zinc-50/50 dark:bg-black/20 backdrop-blur-3xl">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
            <div>
                <h2 class="text-red-600 dark:text-red-500 font-bold tracking-widest text-xs uppercase mb-3">Real-time Radar</h2>
                <h3 class="text-3xl md:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">Live Operations</h3>
            </div>
            <a href="<?php echo e(route('live-map')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white dark:bg-white/10 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white font-bold text-xs hover:scale-105 transition-transform shadow-sm">
                Open Fullscreen Map <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 h-[500px]">
            
            
            <div class="relative bg-zinc-200 dark:bg-zinc-900 rounded-3xl overflow-hidden border border-black/5 dark:border-white/10 shadow-inner z-0">
                <div id="welcome-livemap" class="absolute inset-0 z-0"></div>
                
                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"></div>
            </div>

            
            <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-3xl flex flex-col overflow-hidden shadow-xl shadow-black/5">
                <div class="px-6 py-5 border-b border-black/5 dark:border-white/5 flex justify-between items-center">
                    <span class="font-bold text-zinc-900 dark:text-white text-sm">Airborne Aircraft</span>
                    <span class="px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 font-bold text-[10px] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        <?php echo e(count($mappedFlights)); ?>

                    </span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mappedFlights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div onclick="focusFlight(<?php echo e($f['id']); ?>)" class="p-4 rounded-2xl hover:bg-zinc-50 dark:hover:bg-white/5 cursor-pointer transition-colors border border-transparent hover:border-black/5 dark:hover:border-white/5">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-mono font-bold text-zinc-900 dark:text-white text-sm"><?php echo e($f['flight_number']); ?></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"><?php echo e($f['phase']); ?></span>
                        </div>
                        
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xl font-black tracking-tighter text-zinc-900 dark:text-white"><?php echo e($f['departure']); ?></span>
                            <div class="flex-1 flex items-center">
                                <div class="w-2 h-2 rounded-full border-2 border-red-500"></div>
                                <div class="flex-1 border-t-2 border-dashed border-red-500/30 mx-1"></div>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-red-500" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                                <div class="flex-1 border-t-2 border-dashed border-red-500/30 mx-1"></div>
                                <div class="w-2 h-2 rounded-full border-2 border-red-500 bg-red-500"></div>
                            </div>
                            <span class="text-xl font-black tracking-tighter text-zinc-900 dark:text-white"><?php echo e($f['arrival']); ?></span>
                        </div>

                        <div class="flex justify-between items-center font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
                            <span>&#x2191; <?php echo e($f['altitude'] > 0 ? 'FL'.intdiv($f['altitude'],100) : 'SFC'); ?></span>
                            <span>&#x2192; <?php echo e($f['ground_speed']); ?>kts</span>
                            <span>👨‍✈️ <?php echo e($f['pilot_name']); ?></span>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="flex flex-col items-center justify-center h-full text-center p-8 opacity-50">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-4 text-zinc-400"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Radar is clear.<br>No flights currently airborne.</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>


<section id="stats" class="py-24 relative z-10">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-zinc-900 dark:bg-white/5 rounded-[2.5rem] p-10 md:p-16 relative overflow-hidden shadow-2xl shadow-black/20">
            
            <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 relative z-10">
                <?php $stats = [
                    ['Active Pilots', number_format($pilotCount)],
                    ['Scheduled Routes', number_format($scheduleCount)],
                    ['Total Aircraft', number_format($aircraftCount)],
                    ['Live Now', count($mappedFlights)],
                ]; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="text-center lg:text-left <?php echo e($i < 3 ? 'lg:border-r lg:border-white/10' : ''); ?>">
                    <div class="text-sm font-bold tracking-widest text-zinc-400 dark:text-zinc-500 uppercase mb-2"><?php echo e($s[0]); ?></div>
                    <div class="text-5xl font-black tracking-tighter text-white"><?php echo e($s[1]); ?></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>
</section>


<section class="py-32 relative z-10 overflow-hidden">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-white mb-6">Ready to file your flight plan?</h2>
        <p class="text-xl text-zinc-600 dark:text-zinc-400 mb-12 font-medium max-w-2xl mx-auto">Join a community of aviation enthusiasts pushing the boundaries of flight simulation.</p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center justify-center px-10 py-4 rounded-full bg-red-600 hover:bg-red-700 text-white font-bold text-base transition-transform hover:scale-105 shadow-lg shadow-red-600/30">Head to Dashboard</a>
            <?php else: ?>
                <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center justify-center px-10 py-4 rounded-full bg-red-600 hover:bg-red-700 text-white font-bold text-base transition-transform hover:scale-105 shadow-lg shadow-red-600/30">Start your career now</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>


<footer class="border-t border-black/5 dark:border-white/5 py-12 relative z-10">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-3 opacity-50 hover:opacity-100 transition-opacity">
            <img src="https://iili.io/C32u0Ff.png" alt="FlyAway Logo" class="w-6 h-6 rounded-md object-cover">
            <span class="text-sm font-extrabold tracking-tight text-zinc-900 dark:text-white">FLY<span class="text-red-600">AWAY</span></span>
        </div>
        <p class="text-xs font-semibold text-zinc-500">&copy; <?php echo e(date('Y')); ?> FlyAway Virtual Airline. For simulation purposes only.</p>
    </div>
</footer>


<script>
const FLIGHTS = <?php echo json_encode($mappedFlights, 15, 512) ?>;
let mainMap;
const mainMarkers = {}, mainPaths = {};

function getTileUrl() {
    const isDark = document.documentElement.classList.contains('dark');
    return isDark 
        ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' 
        : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
}

function renderTiles(map) {
    map.eachLayer(layer => {
        if(layer instanceof L.TileLayer) map.removeLayer(layer);
    });
    L.tileLayer(getTileUrl(), { maxZoom: 19, subdomains: 'abcd', attribution: '' }).addTo(map);
}

function planeIcon(heading) {
    return L.divIcon({
        html: `<div style="transform:rotate(${heading}deg); filter:drop-shadow(0 4px 6px rgba(220,38,38,0.5));">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="#dc2626">
                <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L14 19v-5.5L21 16z"/>
            </svg>
        </div>`,
        className: '',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -12]
    });
}

function addFlights(map) {
    FLIGHTS.forEach(f => {
        const pos = [f.current_lat, f.current_lng];
        if (f.breadcrumbs && f.breadcrumbs.length > 1) {
            L.polyline([...f.breadcrumbs, pos], { color: '#dc2626', weight: 2, opacity: 0.5, dashArray: '4,4' }).addTo(map);
        }
        const m = L.marker(pos, { icon: planeIcon(f.heading) }).addTo(map);
        m.bindPopup(`
            <div style="font-family:Inter,sans-serif; min-width:180px; padding:4px;">
                <div style="font-size:16px; font-weight:900; font-family:'JetBrains Mono',monospace; margin-bottom:4px;">${f.flight_number}</div>
                <div style="font-size:12px; opacity:0.7; margin-bottom:12px;">${f.pilot_name}</div>
                <div style="font-size:18px; font-weight:900; margin-bottom:8px;">${f.departure} <span style="color:#dc2626">&rarr;</span> ${f.arrival}</div>
                <div style="font-family:'JetBrains Mono',monospace; font-size:12px; opacity:0.8;">ALT: ${f.altitude} | SPD: ${f.ground_speed}</div>
            </div>
        `);
        mainMarkers[f.id] = m;
    });
    if (FLIGHTS.length > 0) {
        map.fitBounds(L.featureGroup(Object.values(mainMarkers)).getBounds().pad(0.2));
    }
}

window.focusFlight = function(id) {
    const m = mainMarkers[id];
    if (m && mainMap) {
        mainMap.setView(m.getLatLng(), Math.max(mainMap.getZoom(), 6), { animate: true, duration: 1.2 });
        setTimeout(() => m.openPopup(), 600);
    }
};

window.toggleTheme = function() {
    const el = document.documentElement;
    if (el.classList.contains('dark')) {
        el.classList.remove('dark');
        localStorage.theme = 'light';
    } else {
        el.classList.add('dark');
        localStorage.theme = 'dark';
    }
    if (mainMap) renderTiles(mainMap);
};

document.addEventListener('DOMContentLoaded', () => {
    mainMap = L.map('welcome-livemap', { center: [20, 0], zoom: 2, zoomControl: false, attributionControl: false });
    L.control.zoom({ position: 'topright' }).addTo(mainMap);
    renderTiles(mainMap);
    addFlights(mainMap);
});
</script>
</body>
</html>
<?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views/welcome.blade.php ENDPATH**/ ?>