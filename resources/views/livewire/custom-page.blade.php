<?php

use App\Models\CustomPage;
use Livewire\Volt\Component;

new class extends Component {
    public $slug;
    public $page;

    public function mount($slug): void
    {
        $this->slug = $slug;
        $this->page = CustomPage::published()->where('slug', $slug)->firstOrFail();
    }
}; ?>

<div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Floating modern glass card --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/50 shadow-xl overflow-hidden">
            <div class="px-6 py-8 sm:p-10 border-b border-slate-100 dark:border-slate-700/50 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/80 dark:to-slate-800">
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight sm:text-4xl">
                    {{ $page->title }}
                </h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Last updated {{ $page->updated_at->format('M d, Y') }}
                </p>
            </div>
            
            <div class="px-6 py-8 sm:p-10 prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-300">
                {!! $page->content !!}
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="/" class="text-sm font-semibold text-crimson-600 dark:text-crimson-400 hover:text-crimson-500">
                &larr; Return to Home
            </a>
        </div>
    </div>
</div>
