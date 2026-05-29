<?php

namespace App\Notifications;

use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PirepReminder extends Notification
{
    use Queueable;

    public function __construct(public Bid $bid) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Reminder: File a PIREP for {$this->bid->schedule?->flight_number} ({$this->bid->schedule?->departure} → {$this->bid->schedule?->arrival})",
            'flight_number' => $this->bid->schedule?->flight_number,
            'bid_id' => $this->bid->id,
        ];
    }
}
