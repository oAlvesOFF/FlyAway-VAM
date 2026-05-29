<?php

use App\Models\News;
use Livewire\Volt\Component;

new class extends Component {
    public $newsList = [];
    public $showForm = false;
    public $editingId = null;
    public $title = '';
    public $slug = '';
    public $excerpt = '';
    public $content = '';
    public $publishNow = true;

    public function mount(): void
    {
        $this->loadNews();
    }

    public function loadNews(): void
    {
        $this->newsList = News::with('author')->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function edit($id): void
    {
        $news = News::find($id);
        if (!$news) return;
        $this->editingId = $news->id;
        $this->title = $news->title;
        $this->slug = $news->slug;
        $this->excerpt = $news->excerpt ?? '';
        $this->content = $news->content;
        $this->publishNow = $news->published_at !== null;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:news,slug,' . ($this->editingId ?? ''),
            'content' => 'required|string',
        ]);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'published_at' => $this->publishNow ? now() : null,
        ];

        if ($this->editingId) {
            News::find($this->editingId)->update($data);
        } else {
            $data['author_id'] = auth()->id();
            News::create($data);
        }

        $this->showForm = false;
        $this->loadNews();
    }

    public function delete($id): void
    {
        News::find($id)?->delete();
        $this->loadNews();
    }

    public function resetForm(): void
    {
        $this->title = '';
        $this->slug = '';
        $this->excerpt = '';
        $this->content = '';
        $this->publishNow = true;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">News & Announcements</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage airline news, updates, and announcements.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Post</button>
    </div>

    @if($showForm)
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId ? 'Edit Post' : 'New Post' }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
                    <input wire:model="title" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    @error('title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Slug</label>
                    <input wire:model="slug" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    @error('slug') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1 flex items-end">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="publishNow" class="rounded border-slate-300 dark:border-slate-600">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Publish now</span>
                    </label>
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Excerpt</label>
                <textarea wire:model="excerpt" rows="2" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm"></textarea>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Content (Markdown)</label>
                <textarea wire:model="content" rows="10" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-mono"></textarea>
                @error('content') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="save" class="btn-primary text-sm px-4 py-2">Save</button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">Cancel</button>
            </div>
        </div>
    @endif

    <div class="space-y-2">
        @forelse($newsList as $n)
            <div class="card-hover p-4 flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ $n['title'] }}</h3>
                        @if($n['published_at'])
                            <span class="badge-success text-xs">Published</span>
                        @else
                            <span class="badge-warning text-xs">Draft</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">
                        {{ Str::limit($n['excerpt'] ?? 'No excerpt', 100) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $n['author']['name'] ?? 'Unknown' }} &middot; {{ $n['published_at'] ? \Carbon\Carbon::parse($n['published_at'])->format('d M Y') : 'Not published' }}
                    </p>
                </div>
                <div class="flex gap-2 shrink-0 ml-4">
                    <button wire:click="edit({{ $n['id'] }})" class="text-xs text-crimson-600 dark:text-crimson-400 hover:underline">Edit</button>
                    <button wire:click="delete({{ $n['id'] }})" wire:confirm="Delete this post?" class="text-xs text-red-500 hover:underline">Delete</button>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <p>No news posts yet.</p>
            </div>
        @endforelse
    </div>
</div>
