<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $simbrief_username = '';
    public string $simbrief_id = '';
    public string $api_key = '';
    public bool $show_key = false;
    public $avatar = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->simbrief_username = $user->simbrief_username ?? '';
        $this->simbrief_id = $user->simbrief_id ?? '';
        $this->api_key = $user->api_key ?? '';
    }

    public function regenerateApiKey(): void
    {
        $user = Auth::user();
        $newKey = 'fly-' . bin2hex(random_bytes(16));
        $user->update(['api_key' => $newKey]);
        $this->api_key = $newKey;
        $this->show_key = true;
        $this->dispatch('notify', 'API Key regenerated successfully.');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        $this->avatar = null;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'simbrief_username' => ['nullable', 'string', 'max:255'],
            'simbrief_id' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->fill($validated);

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header class="pf-section-header">
        <h2 class="pf-section-title">
            {{ __('Profile Information') }}
        </h2>
        <p class="pf-section-desc">
            {{ __("Update your account's profile information, avatar, and SimBrief settings.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation">
        {{-- Avatar Section --}}
        <div class="pf-avatar-wrap">
            @if(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="pf-avatar-img">
            @else
                <div class="pf-avatar-placeholder">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            @endif
            
            <div style="flex: 1;">
                <label class="pf-label">Profile Photo</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="file" wire:model="avatar" accept="image/*" class="pf-input" style="padding: 6px; font-size: 13px; cursor: pointer;">
                    @if(auth()->user()->avatar)
                        <button type="button" wire:click="removeAvatar" class="pf-btn pf-btn-danger" style="padding: 8px 14px; font-size: 12px;">Remove</button>
                    @endif
                </div>
                <div wire:loading wire:target="avatar" style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Uploading...</div>
                <x-input-error class="pf-error" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div class="pf-form-group">
            <label for="name" class="pf-label">{{ __('Name') }}</label>
            <input wire:model="name" id="name" type="text" class="pf-input" required autofocus autocomplete="name" />
            <x-input-error class="pf-error" :messages="$errors->get('name')" />
        </div>

        <div class="pf-form-group">
            <label for="email" class="pf-label">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" class="pf-input" required autocomplete="username" />
            <x-input-error class="pf-error" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div style="margin-top: 8px;">
                    <p style="font-size: 13px; color: var(--text-secondary);">
                        {{ __('Your email address is unverified.') }}
                        <button wire:click.prevent="sendVerification" style="color: var(--text-badge); background: transparent; border: none; font-weight: 600; cursor: pointer; text-decoration: underline;">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p style="margin-top: 4px; font-size: 13px; font-weight: 600; color: #10b981;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div class="pf-form-group" style="margin-bottom: 0;">
                <label for="simbrief_username" class="pf-label">{{ __('SimBrief Username') }}</label>
                <input wire:model="simbrief_username" id="simbrief_username" type="text" class="pf-input" autocomplete="off" placeholder="your_simbrief_username" />
                <x-input-error class="pf-error" :messages="$errors->get('simbrief_username')" />
            </div>
            <div class="pf-form-group" style="margin-bottom: 0;">
                <label for="simbrief_id" class="pf-label">{{ __('SimBrief Pilot ID (numeric)') }}</label>
                <input wire:model="simbrief_id" id="simbrief_id" type="text" class="pf-input" autocomplete="off" placeholder="e.g. 123456" inputmode="numeric" />
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Find it at SimBrief → Account Settings.</div>
                <x-input-error class="pf-error" :messages="$errors->get('simbrief_id')" />
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-card); padding-top: 20px; margin-bottom: 20px;">
            <h3 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">{{ __('ACARS API Key') }}</h3>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                {{ __('Use this key in your ACARS client settings to authenticate your flights.') }}
            </p>

            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative; flex: 1;">
                    <input 
                        wire:model="api_key" 
                        id="api_key" 
                        type="{{ $show_key ? 'text' : 'password' }}" 
                        class="pf-input" 
                        style="font-family: monospace; letter-spacing: 1px;"
                        readonly 
                    />
                    @if($show_key)
                        <button type="button" wire:click="$set('show_key', false)" style="position: absolute; right: 12px; top: 12px; background: transparent; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="ph-bold ph-eye-slash"></i>
                        </button>
                    @endif
                </div>

                <button type="button" wire:click="regenerateApiKey" class="pf-btn pf-btn-secondary" style="padding: 10px 16px;">
                    <i class="ph-bold ph-arrows-clockwise"></i> {{ __('Regenerate') }}
                </button>

                <button type="button" x-data @click="navigator.clipboard.writeText($wire.api_key); $dispatch('notify', 'Copied to clipboard!')" class="pf-btn pf-btn-secondary" style="padding: 10px; min-width: 40px;" title="Copy to clipboard">
                    <i class="ph-bold ph-copy"></i>
                </button>
            </div>
            <x-input-error class="pf-error" :messages="$errors->get('api_key')" />
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <button type="submit" class="pf-btn">
                <i class="ph-bold ph-floppy-disk"></i> {{ __('Save Changes') }}
            </button>
            <x-action-message on="profile-updated" style="color: #10b981; font-size: 13px; font-weight: 600;">
                <i class="ph-bold ph-check"></i> {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
