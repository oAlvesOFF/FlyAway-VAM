<?php

namespace App\Observers;

use App\Models\User;
use App\Services\DiscordWebhookService;

class UserObserver
{
    public function created(User $user): void
    {
        app(DiscordWebhookService::class)->sendNewPilotRegistration($user);
    }
}
