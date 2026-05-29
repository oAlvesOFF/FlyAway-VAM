<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          init() {
              this.$watch('darkMode', val => {
                  localStorage.setItem('theme', val ? 'dark' : 'light');
                  if (val) document.documentElement.classList.add('dark');
                  else document.documentElement.classList.remove('dark');
              });
              if (this.darkMode) document.documentElement.classList.add('dark');
          }
      }"
      :class="{ 'dark': darkMode }"
      class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Atlantic Star Airways') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        {{-- Logo Area --}}
        <div class="mb-6">
            <a href="/" wire:navigate class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-crimson-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">Atlantic Star</span>
            </a>
        </div>

        {{-- Theme Toggle --}}
        <div class="absolute top-4 right-4">
            <button @click="darkMode = !darkMode" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-all duration-150">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="w-full sm:max-w-md mt-2 px-6 py-6">
            <div class="card p-8">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-4 text-xs text-slate-400 dark:text-slate-600">&copy; {{ date('Y') }} Atlantic Star Airways. All rights reserved.</p>
    </div>

    @livewireScripts
</body>
</html>
