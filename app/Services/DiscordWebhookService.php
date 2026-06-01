<?php

namespace App\Services;

use App\Models\ActiveFlight;
use App\Models\News;
use App\Models\Pirep;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    protected ?string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.discord.webhook_url');
    }

    protected function send(array $embed): void
    {
        if (!$this->webhookUrl) {
            return;
        }

        try {
            Http::timeout(3)->post($this->webhookUrl, [
                'embeds' => [$embed],
            ]);
        } catch (\Exception $e) {
            Log::error('Falha ao enviar webhook para o Discord: ' . $e->getMessage());
        }
    }

    public function sendFlightStatus(ActiveFlight $flight): void
    {
        $colors = [
            'preflight' => 10181046, // Purple
            'boarding' => 3447003,   // Blue
            'departed' => 15105570,  // Orange
            'enroute' => 3447003,    // Blue
            'onapproach' => 15105570,// Orange
            'landed' => 3066993,     // Green
        ];

        $color = $colors[$flight->phase] ?? 10181046;

        $fases = [
            'preflight' => 'Pré-Voo',
            'boarding' => 'Embarque',
            'departed' => 'Decolado',
            'enroute' => 'Em Rota',
            'onapproach' => 'Em Aproximação',
            'landed' => 'Pousado',
        ];

        $fase = $fases[$flight->phase] ?? ucfirst($flight->phase);
        $pilotName = $flight->user ? $flight->user->name : 'Piloto Desconhecido';

        $embed = [
            'title' => "✈️ Atualização de Voo: {$flight->flight_number}",
            'color' => $color,
            'fields' => [
                ['name' => 'Piloto', 'value' => $pilotName, 'inline' => true],
                ['name' => 'Nova Fase', 'value' => $fase, 'inline' => true],
                ['name' => 'Rota', 'value' => "{$flight->departure} ➔ {$flight->arrival}", 'inline' => true],
                ['name' => 'Aeronave', 'value' => $flight->aircraft_icao, 'inline' => true],
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->send($embed);
    }

    public function sendPirepSubmitted(Pirep $pirep): void
    {
        $pilotName = $pirep->user ? $pirep->user->name : 'Piloto Desconhecido';

        $embed = [
            'title' => "📝 Novo PIREP Enviado: {$pirep->flight_number}",
            'color' => 3447003, // Blue
            'fields' => [
                ['name' => 'Piloto', 'value' => $pilotName, 'inline' => true],
                ['name' => 'Rota', 'value' => "{$pirep->departure} ➔ {$pirep->arrival}", 'inline' => true],
                ['name' => 'Tempo de Voo', 'value' => "{$pirep->flight_time}h", 'inline' => true],
                ['name' => 'Taxa de Pouso', 'value' => "{$pirep->landing_rate} fpm", 'inline' => true],
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->send($embed);
    }

    public function sendPirepStatus(Pirep $pirep): void
    {
        $color = $pirep->status === 'approved' ? 3066993 : 15158332; // Green or Red
        $statusStr = $pirep->status === 'approved' ? 'Aprovado' : 'Rejeitado';
        $pilotName = $pirep->user ? $pirep->user->name : 'Piloto Desconhecido';

        $embed = [
            'title' => "📋 PIREP {$statusStr}: {$pirep->flight_number}",
            'color' => $color,
            'fields' => [
                ['name' => 'Piloto', 'value' => $pilotName, 'inline' => true],
                ['name' => 'Rota', 'value' => "{$pirep->departure} ➔ {$pirep->arrival}", 'inline' => true],
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        if ($pirep->status === 'rejected' && $pirep->rejection_reason) {
            $embed['fields'][] = [
                'name' => 'Motivo da Rejeição',
                'value' => $pirep->rejection_reason,
                'inline' => false
            ];
        }

        $this->send($embed);
    }

    public function sendNewPilotRegistration(User $user): void
    {
        $embed = [
            'title' => "👋 Novo Piloto Registrado",
            'description' => "Dê boas-vindas ao mais novo membro da nossa Virtual Airline!",
            'color' => 15844367, // Gold
            'fields' => [
                ['name' => 'Nome', 'value' => $user->name, 'inline' => true],
                ['name' => 'Pilot ID', 'value' => $user->pilot_id ?? 'N/A', 'inline' => true],
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->send($embed);
    }

    public function sendNewsPublished(News $news): void
    {
        $authorName = $news->author ? $news->author->name : 'Administração';

        $embed = [
            'title' => "📰 Nova Notícia Publicada",
            'description' => "**{$news->title}**\n\n{$news->excerpt}",
            'color' => 1752220, // Aqua
            'fields' => [
                ['name' => 'Autor', 'value' => $authorName, 'inline' => true],
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->send($embed);
    }
}
