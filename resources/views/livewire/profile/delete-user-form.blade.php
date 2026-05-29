<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section>
    <header class="pf-section-header">
        <h2 class="pf-section-title" style="color: #ef4444;">
            {{ __('Delete Account') }}
        </h2>
        <p class="pf-section-desc">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="pf-btn pf-btn-danger">
        <i class="ph-bold ph-trash"></i> {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" style="padding: 24px; background: var(--bg-card); color: var(--text-primary); border-radius: 8px;">

            <h2 style="font-size: 18px; font-weight: 800; color: #ef4444; margin-bottom: 8px;">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="pf-form-group">
                <label for="password" class="pf-label">{{ __('Password') }}</label>
                <input wire:model="password" id="password" type="password" class="pf-input" placeholder="{{ __('Enter your password') }}" />
                <x-input-error :messages="$errors->get('password')" class="pf-error" />
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" x-on:click="$dispatch('close')" class="pf-btn pf-btn-secondary">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="pf-btn pf-btn-danger">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
