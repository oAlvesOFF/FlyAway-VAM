<?php

namespace App\Notifications;

use App\Models\Pirep;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PirepRejected extends Notification
{
    use Queueable;

    public function __construct(public Pirep $pirep) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("PIREP {$this->pirep->flight_number} Rejected – Atlantic Star Airways")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your PIREP for flight **{$this->pirep->flight_number}** ({$this->pirep->departure} → {$this->pirep->arrival}) has been rejected.")
            ->when($this->pirep->rejection_reason, fn($msg) => $msg->line("**Reason:** " . $this->pirep->rejection_reason))
            ->line("Please review your submission and file a corrected PIREP.")
            ->action('File a New PIREP', url('/file-pirep'))
            ->line('If you believe this was in error, please contact staff.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pirep_id' => $this->pirep->id,
            'flight_number' => $this->pirep->flight_number,
            'departure' => $this->pirep->departure,
            'arrival' => $this->pirep->arrival,
            'score' => $this->pirep->score,
            'message' => "PIREP {$this->pirep->flight_number} ({$this->pirep->departure}→{$this->pirep->arrival}) was rejected." . ($this->pirep->rejection_reason ? " Reason: {$this->pirep->rejection_reason}" : ""),
        ];
    }
}
