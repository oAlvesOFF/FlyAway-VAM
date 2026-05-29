<?php

namespace App\Console\Commands;

use App\Models\Bid;
use App\Models\Pirep;
use App\Notifications\PirepReminder;
use Illuminate\Console\Command;

class SendPirepReminders extends Command
{
    protected $signature = 'pireps:send-reminders';
    protected $description = 'Send reminders to pilots who have open bookings but no recent PIREP';

    public function handle(): void
    {
        $bids = Bid::with(['user', 'schedule'])->get();
        $sent = 0;

        foreach ($bids as $bid) {
            if (!$bid->user || !$bid->schedule) continue;

            $hasRecentPirep = Pirep::where('user_id', $bid->user_id)
                ->where('flight_number', $bid->schedule->flight_number)
                ->where('created_at', '>=', now()->subDays(3))
                ->exists();

            if ($hasRecentPirep) continue;

            $bid->user->notify(new PirepReminder($bid));
            $sent++;
        }

        $this->info("Sent {$sent} PIREP reminder(s).");
    }
}
