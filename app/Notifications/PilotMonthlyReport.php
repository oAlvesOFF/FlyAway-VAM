<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PilotMonthlyReport extends Notification
{
    use Queueable;

    public function __construct(
        public array $stats,
        public int $month,
        public int $year,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->stats;

        return (new MailMessage)
            ->subject("Your {$this->month}/{$this->year} Flight Report – Atlantic Star Airways")
            ->greeting("Hello {$notifiable->name},")
            ->line("Here's your flight activity summary for **{$this->month}/{$this->year}**:")
            ->line("")
            ->line("✈️ **Flights Logged:** {$s['total_flights']}")
            ->line("⏱️ **Total Hours:** {$s['total_hours']}")
            ->line("📊 **Average Score:** {$s['avg_score']}")
            ->line("🏆 **Best Landing Rate:** {$s['best_landing_rate']} fpm")
            ->line("📍 **Most Visited Airport:** {$s['top_airport']}")
            ->line("🎯 **Achievements Unlocked This Month:** {$s['achievements_count']}")
            ->line("")
            ->when($s['total_flights'] > 0, function ($msg) {
                $msg->line("Keep up the great work! Your dedication to Atlantic Star Airways is appreciated.");
            })
            ->when($s['total_flights'] === 0, function ($msg) {
                $msg->line("You didn't log any flights this month. We'd love to see you in the skies! Book a flight today.");
            })
            ->action('View Your Dashboard', url('/dashboard'))
            ->line("Thank you for flying with Atlantic Star Airways!");
    }
}
