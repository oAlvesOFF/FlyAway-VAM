<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MqttService
{
    protected ?MqttClient $client = null;
    protected bool $enabled = false;
    protected string $host;
    protected int $port;
    protected string $clientId;
    protected ?string $username;
    protected ?string $password;

    public function __construct()
    {
        try {
            if (\Illuminate\Support\Facades\App::has('App\Models\Setting')) {
                $this->enabled = \App\Models\Setting::get('mqtt_enabled') === 'true';
                $this->host = \App\Models\Setting::get('mqtt_host') ?: env('MQTT_HOST', '127.0.0.1');
                $this->port = (int) (\App\Models\Setting::get('mqtt_port') ?: env('MQTT_PORT', 1883));
                $this->username = \App\Models\Setting::get('mqtt_username') ?: env('MQTT_USERNAME', null);
                $this->password = \App\Models\Setting::get('mqtt_password') ?: env('MQTT_PASSWORD', null);
            }
        } catch (\Throwable $e) {
            $this->enabled = env('MQTT_ENABLED', false);
            $this->host = env('MQTT_HOST', '127.0.0.1');
            $this->port = (int) env('MQTT_PORT', 1883);
            $this->username = env('MQTT_USERNAME', null);
            $this->password = env('MQTT_PASSWORD', null);
        }
        $this->clientId = env('MQTT_CLIENT_ID', 'flyaway-server-' . bin2hex(random_bytes(4)));
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->host;
    }

    public function connect(): bool
    {
        if (!$this->isEnabled()) return false;

        try {
            $settings = (new ConnectionSettings)
                ->setUsername($this->username)
                ->setPassword($this->password)
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(5);

            $this->client = new MqttClient($this->host, $this->port, $this->clientId);
            $this->client->connect($settings);
            return true;
        } catch (\Throwable $e) {
            Log::warning('MQTT connection failed: ' . $e->getMessage());
            return false;
        }
    }

    public function disconnect(): void
    {
        if ($this->client && $this->client->isConnected()) {
            try {
                $this->client->disconnect();
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function publish(string $topic, array $data, int $qos = 0): bool
    {
        if (!$this->client || !$this->client->isConnected()) {
            if (!$this->connect()) return false;
        }

        try {
            $this->client->publish($topic, json_encode($data), $qos);
            return true;
        } catch (\Throwable $e) {
            Log::warning('MQTT publish failed: ' . $e->getMessage());
            return false;
        }
    }

    public function subscribe(string $topic, callable $handler, int $qos = 0): bool
    {
        if (!$this->client || !$this->client->isConnected()) {
            if (!$this->connect()) return false;
        }

        try {
            $this->client->subscribe($topic, function ($topic, $message) use ($handler) {
                $data = json_decode($message, true);
                $handler($topic, $data ?? []);
            }, $qos);
            return true;
        } catch (\Throwable $e) {
            Log::warning('MQTT subscribe failed: ' . $e->getMessage());
            return false;
        }
    }

    public function loop(bool $blocking = true): void
    {
        if ($this->client && $this->client->isConnected()) {
            $this->client->loop($blocking);
        }
    }

    public function isConnected(): bool
    {
        return $this->client && $this->client->isConnected();
    }
}
