<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class DiscordNotifier
{
    public static function send(string $message, ?string $title = null, ?string $color = null): void
    {
        $webhook = Setting::get('discord_webhook_url', '');
        if (!$webhook) return;

        $payload = [
            'embeds' => [[
                'description' => $message,
                'color' => $color ? hexdec(ltrim($color, '#')) : 0xe11d48,
                'timestamp' => now()->toIso8601String(),
            ]],
        ];

        if ($title) {
            $payload['embeds'][0]['title'] = $title;
        }

        try {
            Http::timeout(5)->post($webhook, $payload);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Discord webhook failed: {$e->getMessage()}");
        }
    }

    public static function pirepApproved(string $flightNumber, string $dep, string $arr, string $pilotName, int $score): void
    {
        self::send(
            "**{$pilotName}** filed **{$flightNumber}** ({$dep}→{$arr})\nScore: **{$score}** ✅",
            'PIREP Approved',
            '#10b981'
        );
    }

    public static function pirepRejected(string $flightNumber, string $dep, string $arr, string $pilotName, ?string $reason = null): void
    {
        $msg = "**{$pilotName}** filed **{$flightNumber}** ({$dep}→{$arr}) was rejected.";
        if ($reason) $msg .= "\nReason: {$reason}";
        self::send($msg, 'PIREP Rejected', '#ef4444');
    }

    public static function newPilot(string $name, string $pilotId): void
    {
        self::send(
            "**{$name}** ({$pilotId}) just joined the airline!",
            'New Pilot Registered',
            '#6366f1'
        );
    }
}
