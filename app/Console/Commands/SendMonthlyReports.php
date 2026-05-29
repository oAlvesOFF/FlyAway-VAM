<?php

namespace App\Console\Commands;

use App\Models\Achievement;
use App\Models\Pirep;
use App\Models\User;
use App\Notifications\PilotMonthlyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendMonthlyReports extends Command
{
    protected $signature = 'pireps:monthly-report {--month=} {--year=}';
    protected $description = 'Send monthly flight report to all active pilots';

    public function handle(): void
    {
        $month = $this->option('month') ?: now()->subMonth()->month;
        $year = $this->option('year') ?: now()->subMonth()->year;

        $this->info("Generating reports for {$month}/{$year}...");

        $pilots = User::where('status', 'active')->get();
        $sent = 0;

        foreach ($pilots as $pilot) {
            $pireps = Pirep::where('user_id', $pilot->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'approved')
                ->get();

            $totalFlights = $pireps->count();

            if ($totalFlights === 0) {
                $stats = [
                    'total_flights' => 0,
                    'total_hours' => '0.0',
                    'avg_score' => '—',
                    'best_landing_rate' => '—',
                    'top_airport' => '—',
                    'achievements_count' => 0,
                ];
            } else {
                $topAirport = Pirep::select('departure as apt', DB::raw('COUNT(*) as cnt'))
                    ->where('user_id', $pilot->id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'approved')
                    ->groupBy('departure')
                    ->orderBy('cnt', 'desc')
                    ->first();

                $achievementsThisMonth = $pilot->achievements()
                    ->whereYear('achievement_user.created_at', $year)
                    ->whereMonth('achievement_user.created_at', $month)
                    ->count();

                $stats = [
                    'total_flights' => $totalFlights,
                    'total_hours' => number_format($pireps->sum('flight_time'), 1),
                    'avg_score' => number_format($pireps->avg('score'), 0),
                    'best_landing_rate' => $pireps->whereNotNull('landing_rate')->min('landing_rate') ?? '—',
                    'top_airport' => $topAirport?->apt ?? '—',
                    'achievements_count' => $achievementsThisMonth,
                ];
            }

            try {
                $pilot->notify(new PilotMonthlyReport($stats, $month, $year));
                $sent++;
            } catch (\Exception $e) {
                $this->warn("Failed to send to {$pilot->email}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} monthly reports.");
    }
}
