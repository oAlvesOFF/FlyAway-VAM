<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pirep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function pilotsCsv(): StreamedResponse
    {
        $pilots = User::with('rank')->orderBy('created_at', 'desc')->get();

        $response = new StreamedResponse(function () use ($pilots) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Email', 'Pilot ID', 'Rank', 'Total Hours', 'Total Flights', 'Status', 'Location', 'Member Since']);

            foreach ($pilots as $p) {
                fputcsv($handle, [
                    $p->name,
                    $p->email,
                    $p->pilot_id ?? '—',
                    $p->rank?->name ?? 'Candidate',
                    number_format($p->total_hours, 1),
                    $p->total_flights,
                    $p->status,
                    $p->last_location ?? '—',
                    $p->created_at?->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="pilots-' . now()->format('Y-m-d') . '.csv"');

        return $response;
    }

    public function pirepsCsv(): StreamedResponse
    {
        $pireps = Pirep::with('user')->orderBy('created_at', 'desc')->get();

        $response = new StreamedResponse(function () use ($pireps) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ID', 'Pilot', 'Flight', 'Route', 'Aircraft', 'Flight Time', 'Landing Rate', 'Score', 'Status', 'Submitted']);

            foreach ($pireps as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->user?->pilot_id ?? '—',
                    $p->flight_number,
                    $p->departure . ' → ' . $p->arrival,
                    $p->aircraft_registration . ' (' . $p->aircraft_icao . ')',
                    number_format($p->flight_time, 2) . 'h',
                    $p->landing_rate . ' fpm',
                    $p->score . '/100',
                    $p->status,
                    $p->submitted_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="pireps-' . now()->format('Y-m-d') . '.csv"');

        return $response;
    }

    public function pilotsPrint()
    {
        $pilots = User::with('rank')->orderBy('created_at', 'desc')->get();
        return view('admin.exports.pilots-print', compact('pilots'));
    }
}
