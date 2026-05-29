<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    private static ?array $cache = null;

    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, mixed $value, ?string $label = null): void
    {
        Setting::set($key, $value, label: $label);
        self::$cache = null;
    }

    public function all(): array
    {
        if (self::$cache === null) {
            self::$cache = Setting::pluck('value', 'key')->toArray();
        }
        return self::$cache;
    }

    public function vaName(): string
    {
        return $this->get('va_name', 'Atlantic Star Airways');
    }

    public function vaCallsign(): string
    {
        return $this->get('va_callsign', 'ASR');
    }

    public function vaHomeAirport(): string
    {
        return $this->get('va_home_airport', 'YSSY');
    }

    public function vaDescription(): string
    {
        return $this->get('va_description', 'A premier virtual airline operating across the Asia-Pacific region and beyond.');
    }

    public function registrationOpen(): bool
    {
        return $this->get('registration_open', true);
    }

    public function defaultPirepStatus(): string
    {
        return $this->get('default_pirep_status', 'pending');
    }
}
