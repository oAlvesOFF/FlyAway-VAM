<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $page->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-8 prose dark:prose-invert max-w-none">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</x-app-layout>
