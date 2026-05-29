<?php

use App\Models\Permission;
use App\Models\Role;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $editingRole = null;
    public $showForm = false;
    public $name = '';
    public $slug = '';
    public $description = '';
    public $is_staff = false;
    public $selectedPermissions = [];

    public function mount(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingRole = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->is_staff = false;
        $this->selectedPermissions = [];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->editingRole = $role->id;
        $this->name = $role->name;
        $this->slug = $role->slug;
        $this->description = $role->description;
        $this->is_staff = $role->is_staff;
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . ($this->editingRole ?? 'NULL'),
            'description' => 'nullable|string|max:500',
        ]);

        $role = $this->editingRole
            ? Role::findOrFail($this->editingRole)
            : new Role();

        $role->name = $this->name;
        $role->slug = $this->slug;
        $role->description = $this->description;
        $role->is_staff = $this->is_staff;
        $role->save();

        $role->permissions()->sync(array_filter($this->selectedPermissions));

        session()->flash('success', $this->editingRole ? 'Role updated.' : 'Role created.');
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete($id): void
    {
        $role = Role::findOrFail($id);
        if ($role->users()->exists()) {
            session()->flash('error', 'Cannot delete role with assigned users.');
            return;
        }
        $role->permissions()->detach();
        $role->delete();
        session()->flash('success', 'Role deleted.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function with(): array
    {
        return [
            'roles' => Role::withCount('users')->with('permissions')->orderBy('name')->paginate(10),
            'allPermissions' => Permission::orderBy('group')->orderBy('name')->get()->groupBy('group'),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Staff Roles</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage roles and permissions for staff members.</p>
        </div>
        <button wire:click="create" class="btn-primary text-sm px-4 py-2">+ New Role</button>
    </div>

    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 p-4 text-red-700 dark:text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    @if($showForm)
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">{{ $editingRole ? 'Edit Role' : 'New Role' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
                        <input wire:model="name" class="input w-full" placeholder="e.g. Chief Pilot">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug</label>
                        <input wire:model="slug" class="input w-full" placeholder="e.g. chief-pilot">
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea wire:model="description" rows="2" class="input w-full" placeholder="Role description..."></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_staff" id="is_staff" class="rounded border-slate-300 text-crimson-600 focus:ring-crimson-500">
                    <label for="is_staff" class="text-sm text-slate-700 dark:text-slate-300">Staff Role (access to staff center)</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Permissions</label>
                    <div class="grid md:grid-cols-3 gap-3">
                        @foreach($allPermissions as $group => $perms)
                            <div class="card p-3 bg-slate-50 dark:bg-slate-800/50">
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">{{ $group ?: 'General' }}</p>
                                @foreach($perms as $perm)
                                    <label class="flex items-center gap-2 py-1 cursor-pointer">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->id }}" class="rounded border-slate-300 text-crimson-600 focus:ring-crimson-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $perm->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary text-sm px-6 py-2">{{ $editingRole ? 'Update' : 'Create' }}</button>
                    <button type="button" wire:click="cancel" class="btn-secondary text-sm px-4 py-2">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <th class="pb-3 font-medium">Role</th>
                        <th class="pb-3 font-medium">Slug</th>
                        <th class="pb-3 font-medium">Staff</th>
                        <th class="pb-3 font-medium">Users</th>
                        <th class="pb-3 font-medium">Permissions</th>
                        <th class="pb-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="py-3 font-medium text-slate-900 dark:text-white">{{ $role->name }}</td>
                            <td class="py-3 text-slate-500">{{ $role->slug }}</td>
                            <td class="py-3">{!! $role->is_staff ? '<span class="badge-success">Yes</span>' : '<span class="text-slate-400">No</span>' !!}</td>
                            <td class="py-3 text-slate-500">{{ $role->users_count }}</td>
                            <td class="py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($role->permissions as $perm)
                                        <span class="text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-slate-600 dark:text-slate-300">{{ $perm->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 text-right">
                                <button wire:click="edit({{ $role->id }})" class="text-sm text-crimson-600 dark:text-crimson-400 hover:underline font-medium mr-3">Edit</button>
                                @if($role->users_count === 0)
                                    <button wire:click="delete({{ $role->id }})" wire:confirm="Delete this role?" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $roles->links() }}</div>
    </div>
</div>
