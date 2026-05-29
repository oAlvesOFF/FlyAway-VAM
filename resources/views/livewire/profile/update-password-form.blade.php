<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header class="pf-section-header">
        <h2 class="pf-section-title">
            {{ __('Update Password') }}
        </h2>
        <p class="pf-section-desc">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form wire:submit="updatePassword">
        <div class="pf-form-group">
            <label for="update_password_current_password" class="pf-label">{{ __('Current Password') }}</label>
            <input wire:model="current_password" id="update_password_current_password" type="password" class="pf-input" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="pf-error" />
        </div>

        <div class="pf-form-group">
            <label for="update_password_password" class="pf-label">{{ __('New Password') }}</label>
            <input wire:model="password" id="update_password_password" type="password" class="pf-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="pf-error" />
        </div>

        <div class="pf-form-group">
            <label for="update_password_password_confirmation" class="pf-label">{{ __('Confirm Password') }}</label>
            <input wire:model="password_confirmation" id="update_password_password_confirmation" type="password" class="pf-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="pf-error" />
        </div>

        <div style="display: flex; align-items: center; gap: 16px; margin-top: 24px;">
            <button type="submit" class="pf-btn">
                <i class="ph-bold ph-lock-key"></i> {{ __('Save Password') }}
            </button>

            <x-action-message on="password-updated" style="color: #10b981; font-size: 13px; font-weight: 600;">
                <i class="ph-bold ph-check"></i> {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
