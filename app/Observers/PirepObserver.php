<?php

namespace App\Observers;

use App\Models\Pirep;
use App\Services\DiscordWebhookService;

class PirepObserver
{
    public function created(Pirep $pirep): void
    {
        app(DiscordWebhookService::class)->sendPirepSubmitted($pirep);
    }

    // Aprovação e Rejeição são tratadas diretamente no PirepController
    // para contornar o bug do isDirty() que retorna false após update().
}
