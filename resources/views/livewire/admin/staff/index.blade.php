<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function assignRole($userId, $roleId): void
    {
        $user = User::findOrFail($userId);
        $role = $roleId ? Role::findOrFail($roleId) : null;
        $user->update(['role_id' => $role?->id]);
        session()->flash('success', "Role updated for {$user->name}.");
    }

    public function with(): array
    {
        return [
            'staff' => User::with('role')
                ->where(function ($q) {
                    $q->where('is_admin', true)
                      ->orWhereHas('role', fn($q) => $q->where('is_staff', true));
                })
                ->when($this->search, fn($q) => $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('pilot_id', 'like', "%{$this->search}%");
                }))
                ->orderBy('name')
                ->paginate(10),
            'roles' => Role::where('is_staff', true)->orWhere('slug', 'admin')->orderBy('name')->get(),
            'allUsers' => User::whereDoesntHave('role', fn($q) => $q->where('is_staff', true))
                ->where('is_admin', false)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('pilot_id', 'like', "%{$this->search}%");
                })
                ->orderBy('name')
                ->paginate(10, ['*'], 'usersPage'),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Staff Management</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Assign roles to staff members.</p>
    </div>

    @if(session('success'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif

    <div>
        <input wire:model.live.debounce="search" type="text" placeholder="Search by name, email, or pilot ID..." class="input w-full max-w-md">
    </div>

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Current Staff</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <th class="pb-3 font-medium">Pilot</th>
                        <th class="pb-3 font-medium">ID</th>
                        <th class="pb-3 font-medium">Admin</th>
                        <th class="pb-3 font-medium">Role</th>
                        <th class="pb-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $user)
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="py-3 font-medium text-slate-900 dark:text-white">{{ $user->name }}</td>
                            <td class="py-3 text-slate-500">{{ $user->pilot_id }}</td>
                            <td class="py-3">{!! $user->is_admin ? '<span class="badge-success">Yes</span>' : '<span class="text-slate-400">No</span>' !!}</td>
                            <td class="py-3">
                                <select wire:change="assignRole({{ $user->id }}, $event.target.value)" class="input text-xs py-1">
                                    <option value="">No Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-3 text-right">
                                @if(!$user->is_admin)
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $staff->links() }}</div>
    </div>

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">All Pilots</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <th class="pb-3 font-medium">Pilot</th>
                        <th class="pb-3 font-medium">ID</th>
                        <th class="pb-3 font-medium">Hours</th>
                        <th class="pb-3 font-medium">Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allUsers as $user)
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="py-3 font-medium text-slate-900 dark:text-white">{{ $user->name }}</td>
                            <td class="py-3 text-slate-500">{{ $user->pilot_id }}</td>
                            <td class="py-3 text-slate-500">{{ number_format($user->total_hours, 1) }}</td>
                            <td class="py-3">
                                <select wire:change="assignRole({{ $user->id }}, $event.target.value)" class="input text-xs py-1">
                                    <option value="">No Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $allUsers->links() }}</div>
    </div>
</div>
