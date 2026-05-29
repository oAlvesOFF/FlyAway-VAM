<?php

use App\Models\Setting;
use Livewire\Volt\Component;

new class extends Component {
    public array $settings = [];

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $all = Setting::all()->keyBy('key')->toArray();
        $defaults = [
            'va_name' => ['value' => 'Atlantic Star Airways', 'type' => 'string', 'label' => 'Airline Name'],
            'va_callsign' => ['value' => 'ASR', 'type' => 'string', 'label' => 'Callsign'],
            'va_home_airport' => ['value' => 'YSSY', 'type' => 'string', 'label' => 'Home Airport'],
            'va_description' => ['value' => 'A premier virtual airline operating across the Asia-Pacific region.', 'type' => 'text', 'label' => 'Description'],
            'registration_open' => ['value' => 'true', 'type' => 'boolean', 'label' => 'Open Registration'],
            'mqtt_enabled' => ['value' => 'false', 'type' => 'boolean', 'label' => 'MQTT Bridge Enabled'],
            'mqtt_host' => ['value' => '127.0.0.1', 'type' => 'string', 'label' => 'MQTT Broker Host'],
            'mqtt_port' => ['value' => '1883', 'type' => 'string', 'label' => 'MQTT Broker Port'],
            'mqtt_username' => ['value' => '', 'type' => 'string', 'label' => 'MQTT Username'],
            'mqtt_password' => ['value' => '', 'type' => 'string', 'label' => 'MQTT Password'],
            'auto_approve_threshold' => ['value' => '90', 'type' => 'string', 'label' => 'Auto-Approve Score Threshold'],
            'discord_webhook_url' => ['value' => '', 'type' => 'string', 'label' => 'Discord Webhook URL'],
        ];

        foreach ($defaults as $key => $def) {
            $existing = $all[$key] ?? null;
            $this->settings[$key] = [
                'id' => $existing['id'] ?? null,
                'value' => $existing ? $existing['value'] : $def['value'],
                'type' => $existing ? $existing['type'] : $def['type'],
                'label' => $existing ? $existing['label'] : $def['label'],
            ];
        }
    }

    public function save(): void
    {
        foreach ($this->settings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data['value'], 'type' => $data['type'], 'label' => $data['label']]
            );
        }

        session()->flash('saved', 'Settings saved successfully.');
    }
}; ?>

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Airline Settings</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Configure your virtual airline's global settings.</p>
    </div>

    @if(session('saved'))
        <div class="card bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 p-4 text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('saved') }}
        </div>
    @endif

    <div class="card p-6 space-y-5">
        @foreach($settings as $key => $data)
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $data['label'] }}</label>
                @if($data['type'] === 'boolean')
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="settings.{{ $key }}.value" value="true" class="rounded border-slate-300 dark:border-slate-600" {{ $data['value'] === 'true' ? 'checked' : '' }}>
                        <span class="text-sm text-slate-500">Enabled</span>
                    </div>
                @elseif($data['type'] === 'text')
                    <textarea wire:model="settings.{{ $key }}.value" rows="3" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm"></textarea>
                @else
                    <input wire:model="settings.{{ $key }}.value" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                @endif
                <p class="text-xs text-slate-400 font-mono">{{ $key }}</p>
            </div>
        @endforeach

        <button wire:click="save" class="btn-primary text-sm px-6 py-2">Save Settings</button>
    </div>
</div>
