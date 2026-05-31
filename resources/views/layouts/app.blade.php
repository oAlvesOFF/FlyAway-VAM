<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          sidebarOpen: window.innerWidth >= 1024,
          isMobile: window.innerWidth < 1024,
          userMenuOpen: false,
          init() {
              this.$watch('darkMode', val => {
                  localStorage.setItem('theme', val ? 'dark' : 'light');
                  if (val) document.documentElement.classList.add('dark');
                  else document.documentElement.classList.remove('dark');
              });
              if (this.darkMode) document.documentElement.classList.add('dark');
              const onResize = () => {
                  const mobile = window.innerWidth < 1024;
                  if (mobile !== this.isMobile) {
                      this.isMobile = mobile;
                      this.sidebarOpen = !mobile;
                  }
              };
              window.addEventListener('resize', onResize);
          }
      }"
      x-on:livewire:navigated.window="
          if (darkMode) document.documentElement.classList.add('dark');
          else document.documentElement.classList.remove('dark');
          if (isMobile) sidebarOpen = false;
      "
      :class="{ 'dark': darkMode }"
      class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Atlantic Star Airways') }}</title>
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Phosphor Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.min.css" />
    {{-- App styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        /* Sidebar smooth transition */
        #app-sidebar {
            transition: width 0.3s ease, transform 0.3s ease;
        }
        #app-main {
            transition: margin-left 0.3s ease;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">

    {{-- ===================== MOBILE OVERLAY ===================== --}}
    <div
        x-show="isMobile && sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        style="display:none"
        class="fixed inset-0 z-30 bg-black/60"
        aria-hidden="true">
    </div>

    {{-- ===================== SIDEBAR ===================== --}}
    <aside
        id="app-sidebar"
        :style="isMobile
            ? (sidebarOpen ? 'transform:translateX(0);width:17rem;' : 'transform:translateX(-100%);width:17rem;')
            : (sidebarOpen ? 'width:16rem;transform:translateX(0);' : 'width:4.5rem;transform:translateX(0);')"
        :class="{ 'pointer-events-none': isMobile && !sidebarOpen }"
        class="fixed left-0 top-0 h-full flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-40 overflow-hidden shadow-xl">

        {{-- Sidebar Top: Logo + Close (mobile) --}}
        <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-800 shrink-0 gap-3">
            <img src="https://iili.io/C32u0Ff.png" alt="Logo" class="w-8 h-8 rounded-xl object-cover shrink-0">
            <span x-show="sidebarOpen || isMobile" class="flex-1 text-base font-bold text-slate-900 dark:text-white whitespace-nowrap overflow-hidden">Atlantic Star</span>
            {{-- X button only on mobile --}}
            <button
                x-show="isMobile && sidebarOpen"
                @click="sidebarOpen = false"
                class="ml-auto p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0"
                aria-label="Fechar menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

            @php
                $navItem = function(string $route, string $label, string $iconPath, string $activePattern = '') use (&$navItem) {
                    // helper not used inline — links built directly below
                };
                $isActive = fn(string $pattern) => request()->routeIs($pattern);
                $linkClass = fn(bool $active) => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 ' .
                    ($active ? 'bg-crimson-50 dark:bg-crimson-950/50 text-crimson-700 dark:text-crimson-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800');
            @endphp

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" wire:navigate class="{{ $linkClass($isActive('dashboard')) }}" title="Dashboard">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                <span x-show="sidebarOpen" class="truncate">Dashboard</span>
            </a>

            {{-- Flights --}}
            <a href="{{ route('flights') }}" wire:navigate class="{{ $linkClass($isActive('flights')) }}" title="Flights">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                <span x-show="sidebarOpen" class="truncate">Flights</span>
            </a>

            {{-- My Bookings --}}
            <a href="{{ route('my-bookings') }}" wire:navigate class="{{ $linkClass($isActive('my-bookings')) }}" title="My Bookings">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                <span x-show="sidebarOpen" class="truncate">My Bookings</span>
            </a>

            {{-- File PIREP --}}
            <a href="{{ route('file-pirep') }}" wire:navigate class="{{ $linkClass($isActive('file-pirep')) }}" title="File PIREP">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <span x-show="sidebarOpen" class="truncate">File PIREP</span>
            </a>

            {{-- My PIREPs --}}
            <a href="{{ route('my-pireps') }}" wire:navigate class="{{ $linkClass($isActive('my-pireps')) }}" title="My PIREPs">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0a2.251 2.251 0 00-2.15 1.586m0 0H9"/></svg>
                <span x-show="sidebarOpen" class="truncate">My PIREPs</span>
            </a>

            {{-- My Stats --}}
            <a href="{{ route('pilot-stats') }}" wire:navigate class="{{ $linkClass($isActive('pilot-stats')) }}" title="My Stats">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                <span x-show="sidebarOpen" class="truncate">My Stats</span>
            </a>

            {{-- SimBrief --}}
            <a href="{{ route('simbrief') }}" wire:navigate class="{{ $linkClass($isActive('simbrief')) }}" title="SimBrief">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                <span x-show="sidebarOpen" class="truncate">SimBrief</span>
            </a>

            {{-- ACARS --}}
            <a href="{{ route('acars') }}" wire:navigate class="{{ $linkClass($isActive('acars')) }}" title="ACARS">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                <span x-show="sidebarOpen" class="truncate">ACARS</span>
            </a>

            {{-- Airports --}}
            <a href="{{ route('airports') }}" wire:navigate class="{{ $linkClass($isActive('airports')) }}" title="Airports">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <span x-show="sidebarOpen" class="truncate">Airports</span>
            </a>

            {{-- Live Map --}}
            <a href="{{ route('live-map') }}" wire:navigate class="{{ $linkClass($isActive('live-map')) }}" title="Live Map">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                <span x-show="sidebarOpen" class="truncate">Live Map</span>
            </a>

            {{-- Leaderboard --}}
            <a href="{{ route('leaderboard') }}" wire:navigate class="{{ $linkClass($isActive('leaderboard')) }}" title="Leaderboard">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 01-2.77.896m0 0a6.025 6.025 0 01-2.77-.896"/></svg>
                <span x-show="sidebarOpen" class="truncate">Leaderboard</span>
            </a>

            {{-- Pilot Roster --}}
            <a href="{{ route('pilot-roster') }}" wire:navigate class="{{ $linkClass($isActive('pilot-roster')) }}" title="Pilot Roster">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                <span x-show="sidebarOpen" class="truncate">Pilot Roster</span>
            </a>

            {{-- Handbook --}}
            <a href="{{ route('handbook') }}" wire:navigate class="{{ $linkClass($isActive('handbook')) }}" title="Handbook">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <span x-show="sidebarOpen" class="truncate">Handbook</span>
            </a>

            {{-- Achievements --}}
            <a href="{{ route('achievements') }}" wire:navigate class="{{ $linkClass($isActive('achievements')) }}" title="Achievements">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                <span x-show="sidebarOpen" class="truncate">Achievements</span>
            </a>

            {{-- Tours --}}
            <a href="{{ route('tours') }}" wire:navigate class="{{ $linkClass($isActive('tours')) }}" title="Tours">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                <span x-show="sidebarOpen" class="truncate">Tours</span>
            </a>

            {{-- Flight History --}}
            <a href="{{ route('flight-history') }}" wire:navigate class="{{ $linkClass($isActive('flight-history')) }}" title="Flight History">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span x-show="sidebarOpen" class="truncate">Flight History</span>
            </a>

            @auth
                @if(auth()->user()->isStaff())
                <div class="pt-3 mt-2 border-t border-slate-200 dark:border-slate-800">
                    <p x-show="sidebarOpen" class="px-3 pb-1.5 text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Staff</p>
                    <a href="{{ route('staff.dashboard') }}" wire:navigate class="{{ $linkClass($isActive('staff.*')) }}" title="Staff Center">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Staff Center</span>
                    </a>
                </div>
                @endif

                @if(auth()->user()->is_admin)
                <div class="pt-3 mt-2 border-t border-slate-200 dark:border-slate-800">
                    <p x-show="sidebarOpen" class="px-3 pb-1.5 text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Admin</p>
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="{{ $linkClass(false) }}" title="Admin Panel">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Admin Panel</span>
                    </a>
                    <a href="{{ route('admin.fleet') }}" wire:navigate class="{{ $linkClass(false) }}" title="Fleet">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Fleet</span>
                    </a>
                    <a href="{{ route('admin.schedules') }}" wire:navigate class="{{ $linkClass(false) }}" title="Schedules">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.251 2.251 0 012.366 1.889m-5.8 0A2.251 2.251 0 0012.75 2.25H12a2.251 2.251 0 00-2.366 1.889"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Schedules</span>
                    </a>
                    <a href="{{ route('admin.pilots') }}" wire:navigate class="{{ $linkClass(false) }}" title="Pilots">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Pilots</span>
                    </a>
                    <a href="{{ route('admin.news') }}" wire:navigate class="{{ $linkClass(false) }}" title="News">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">News</span>
                    </a>
                    <a href="{{ route('admin.pages') }}" wire:navigate class="{{ $linkClass(false) }}" title="Pages">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Pages</span>
                    </a>
                    <a href="{{ route('admin.activity-log') }}" wire:navigate class="{{ $linkClass(false) }}" title="Activity Log">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Activity Log</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" wire:navigate class="{{ $linkClass(false) }}" title="Settings">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Settings</span>
                    </a>
                    <a href="/api/documentation" target="_blank" class="{{ $linkClass(false) }}" title="API Docs">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <span x-show="sidebarOpen" class="truncate">API Docs</span>
                    </a>
                </div>
                @endif
            @endauth
        </nav>

        {{-- Sidebar Footer: Desktop collapse toggle only --}}
        <div class="border-t border-slate-200 dark:border-slate-800 p-2" x-show="!isMobile">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-xl text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'">
                {{-- Collapse icon (arrows left) --}}
                <svg x-show="sidebarOpen" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/>
                </svg>
                {{-- Expand icon (arrows right) --}}
                <svg x-show="!sidebarOpen" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5"/>
                </svg>
                <span x-show="sidebarOpen" class="text-xs whitespace-nowrap">Collapse</span>
            </button>
        </div>
    </aside>

    {{-- ===================== MAIN CONTENT ===================== --}}
    <div id="app-main"
         :style="isMobile ? 'margin-left:0' : (sidebarOpen ? 'margin-left:16rem' : 'margin-left:4.5rem')"
         class="flex flex-col min-h-screen min-w-0 overflow-x-hidden">

        {{-- Top Header --}}
        <header class="sticky top-0 z-20 h-16 bg-white/90 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 backdrop-blur-xl">
            <div class="h-full px-4 lg:px-6 flex items-center justify-between gap-4">

                {{-- Left: hamburger (mobile only) --}}
                <div class="flex items-center">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        x-show="isMobile"
                        class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        aria-label="Toggle menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>
                </div>

                {{-- Right: actions --}}
                <div class="flex items-center gap-2">
                    @auth
                    {{-- Notifications --}}
                    <div class="relative" x-data="{
                        open: false,
                        unread: 0,
                        notifications: [],
                        async fetchNotifs() {
                            try {
                                const res = await fetch('/notifications/unread');
                                const data = await res.json();
                                this.unread = data.unread;
                                this.notifications = data.notifications;
                            } catch(e) {}
                        },
                        init() {
                            this.fetchNotifs();
                            setInterval(() => this.fetchNotifs(), 30000);
                        }
                    }">
                        <button @click="open = !open; if(open) fetchNotifs()"
                                class="relative p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                                title="Notifications">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            <span x-show="unread > 0" x-text="unread"
                                  class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-crimson-600 text-white text-[10px] font-bold px-1"></span>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 z-50 max-h-96 overflow-y-auto">
                            <div class="p-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">Notifications</span>
                                <template x-if="unread > 0">
                                    <button @click="fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => { unread = 0; notifications = []; })"
                                            class="text-xs text-crimson-600 dark:text-crimson-400 hover:underline">Mark all read</button>
                                </template>
                            </div>
                            <template x-if="notifications.length === 0">
                                <div class="p-6 text-center text-slate-400 text-sm">No notifications</div>
                            </template>
                            <template x-for="n in notifications" :key="n.id">
                                <div class="p-3 border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer"
                                     @click="open = false; window.location = '/my-pireps'">
                                    <p class="text-sm text-slate-700 dark:text-slate-300" x-text="n.data.message"></p>
                                    <p class="text-xs text-slate-400 mt-1" x-text="new Date(n.created_at).toLocaleString()"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                    @endauth

                    {{-- Theme Toggle --}}
                    <button @click="darkMode = !darkMode"
                            class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                            title="Toggle theme">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                        </svg>
                    </button>

                    {{-- User Menu --}}
                    @auth
                    <div class="relative" @click.outside="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2.5 pl-2 border-l border-slate-200 dark:border-slate-800 cursor-pointer">
                            <div class="hidden sm:block text-right">
                                <p class="text-sm font-medium text-slate-900 dark:text-white leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->pilot_id }}</p>
                            </div>
                            @if(auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="w-9 h-9 rounded-xl object-cover">
                            @else
                                <div class="w-9 h-9 rounded-xl bg-crimson-100 dark:bg-crimson-900/50 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-crimson-700 dark:text-crimson-400">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                </div>
                            @endif
                        </button>
                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
                            <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary text-sm px-4 py-2">Login</a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 lg:p-6">
            @isset($slot)
                {{ $slot }}
            @endisset
            @hasSection('content')
                @yield('content')
            @endif
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
