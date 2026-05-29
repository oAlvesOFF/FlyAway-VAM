<?php

namespace App\Notifications;

use App\Models\Pirep;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PirepApproved extends Notification
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
            ->subject("PIREP {$this->pirep->flight_number} Approved – Atlantic Star Airways")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your PIREP for flight **{$this->pirep->flight_number}** ({$this->pirep->departure} → {$this->pirep->arrival}) has been approved!")
            ->line("**Flight Time:** {$this->pirep->flight_time} hours")
            ->line("**Landing Rate:** {$this->pirep->landing_rate} fpm")
            ->line("**Score:** {$this->pirep->score}/100")
            ->action('View My PIREPs', url('/my-pireps'))
            ->line('Thank you for flying with Atlantic Star Airways!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pirep_id' => $this->pirep->id,
            'flight_number' => $this->pirep->flight_number,
            'departure' => $this->pirep->departure,
            'arrival' => $this->pirep->arrival,
            'score' => $this->pirep->score,
            'message' => "PIREP {$this->pirep->flight_number} ({$this->pirep->departure}→{$this->pirep->arrival}) has been approved!",
        ];
    }
}
