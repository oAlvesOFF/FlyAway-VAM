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
    public string $api_key = '';
    public bool $show_key = false;
    public $avatar = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->simbrief_username = $user->simbrief_username ?? '';
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
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div class="flex items-center gap-4">
            <div class="shrink-0">
                @if(auth()->user()->avatar)
                    <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700">
                @else
                    <div class="w-20 h-20 rounded-full bg-crimson-100 dark:bg-crimson-900/30 flex items-center justify-center text-2xl font-bold text-crimson-600 dark:text-crimson-400 border-2 border-slate-200 dark:border-slate-700">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</label>
                <input type="file" wire:model="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-crimson-50 file:text-crimson-600 hover:file:bg-crimson-100 dark:file:bg-crimson-900/30 dark:file:text-crimson-400">
                @if(auth()->user()->avatar)
                    <button type="button" wire:click="removeAvatar" class="text-xs text-red-600 hover:underline">Remove photo</button>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                <div wire:loading wire:target="avatar" class="text-xs text-slate-500">Uploading...</div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="simbrief_username" :value="__('SimBrief Username')" />
            <x-text-input wire:model="simbrief_username" id="simbrief_username" name="simbrief_username" type="text" class="mt-1 block w-full" autocomplete="off" placeholder="Your SimBrief username for OFP fetching" />
            <x-input-error class="mt-2" :messages="$errors->get('simbrief_username')" />
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('ACARS API Key') }}</h3>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                {{ __('Use this key in your ACARS client settings to authenticate your flights.') }}
            </p>

            <div class="mt-3 flex items-center gap-2">
                <div class="relative flex-1">
                    <x-text-input 
                        wire:model="api_key" 
                        id="api_key" 
                        type="{{ $show_key ? 'text' : 'password' }}" 
                        class="block w-full font-mono text-sm pr-10" 
                        readonly 
                    />
                    @if($show_key)
                        <button type="button" wire:click="$set('show_key', false)" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    @endif
                </div>

                <button type="button" wire:click="regenerateApiKey" class="px-3 py-2 text-xs font-semibold text-white bg-crimson-600 rounded-lg hover:bg-crimson-700 dark:bg-crimson-500 dark:hover:bg-crimson-400 whitespace-nowrap">
                    {{ __('Regenerate') }}
                </button>

                <button type="button" 
                    x-data 
                    @click="navigator.clipboard.writeText($wire.api_key); $dispatch('notify', 'Copied to clipboard!')" 
                    class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                    title="{{ __('Copy to clipboard') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3 0v2.25m3-4.5v2.25m-9-1.5h10.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H4.5a1.5 1.5 0 00-1.5 1.5v12a1.5 1.5 0 001.5 1.5zm6-13.5h3a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5h-3" />
                    </svg>
                </button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('api_key')" />
        </div>

            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
