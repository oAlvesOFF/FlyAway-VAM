<?php

use App\Models\CustomPage;
use Livewire\Volt\Component;

new class extends Component {
    public $pages = [];
    public $editingId = null;
    public $title = '';
    public $slug = '';
    public $content = '';
    public $published = true;

    public function mount(): void
    {
        $this->loadPages();
    }

    public function loadPages(): void
    {
        $this->pages = CustomPage::orderBy('order')->orderBy('title')->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = 'new';
    }

    public function edit($id): void
    {
        $page = CustomPage::findOrFail($id);
        $this->editingId = $id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->content = $page->content;
        $this->published = $page->published;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'content' => 'required|string',
        ]);

        if ($this->editingId === 'new') {
            CustomPage::create([
                'title' => $this->title,
                'slug' => $this->slug,
                'content' => $this->content,
                'published' => $this->published,
            ]);
            session()->flash('success', 'Page created.');
        } else {
            CustomPage::findOrFail($this->editingId)->update([
                'title' => $this->title,
                'slug' => $this->slug,
                'content' => $this->content,
                'published' => $this->published,
            ]);
            session()->flash('success', 'Page updated.');
        }

        $this->editingId = null;
        $this->loadPages();
    }

    public function delete($id): void
    {
        CustomPage::findOrFail($id)->delete();
        $this->loadPages();
        session()->flash('success', 'Page deleted.');
    }

    public function resetForm(): void
    {
        $this->title = '';
        $this->slug = '';
        $this->content = '';
        $this->published = true;
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->resetForm();
    }
}; ?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Custom Pages</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create and manage public pages.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">New Page</button>
    </div>

    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Editor --}}
    @if($editingId !== null)
    <div class="card p-6 space-y-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId === 'new' ? 'New Page' : 'Edit Page' }}</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
                <input wire:model.live="title" class="input-field w-full" placeholder="About Us">
                @error('title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Slug</label>
                <input wire:model="slug" class="input-field w-full font-mono" placeholder="about-us">
                @error('slug') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400">URL: /page/{{ $slug }}</p>
            </div>
        </div>
        <div class="space-y-1">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Content (HTML allowed)</label>
            <textarea wire:model="content" rows="15" class="input-field w-full font-mono text-sm" placeholder="Write your page content here..."></textarea>
            @error('content') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model="published" id="published" class="rounded border-slate-300 dark:border-slate-600">
            <label for="published" class="text-sm text-slate-700 dark:text-slate-300">Published</label>
        </div>
        <div class="flex gap-3">
            <button wire:click="save" class="btn-primary text-sm px-6">Save</button>
            <button wire:click="cancel" class="btn-secondary text-sm">Cancel</button>
        </div>
    </div>
    @endif

    {{-- Pages List --}}
    <div class="card overflow-hidden">
        @if(count($pages) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="p-4 font-medium">Title</th>
                        <th class="p-4 font-medium">Slug</th>
                        <th class="p-4 font-medium">Status</th>
                        <th class="p-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $page)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $page->title }}</td>
                        <td class="p-4 text-slate-500 font-mono">/page/{{ $page->slug }}</td>
                        <td class="p-4">
                            @if($page->published)
                                <span class="badge-success">Published</span>
                            @else
                                <span class="badge-warning">Draft</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <a href="/page/{{ $page->slug }}" target="_blank" class="text-sm text-slate-500 hover:underline mr-3">View</a>
                            <button wire:click="edit({{ $page->id }})" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline mr-3">Edit</button>
                            <button wire:click="delete({{ $page->id }})" wire:confirm="Delete this page?" class="text-sm text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-slate-400">
            <p>No custom pages yet.</p>
            <button wire:click="create" class="btn-primary mt-4 text-sm">Create your first page</button>
        </div>
        @endif
    </div>
</div>
