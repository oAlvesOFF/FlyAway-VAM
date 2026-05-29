<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveFlight;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenApi\Attributes as OA;

class FlightStreamController extends Controller
{
    #[OA\Get(
        path: '/api/flights/stream',
        summary: 'Stream active flights',
        description: 'Streams real-time position updates for all active flights using Server-Sent Events (SSE).',
        tags: ['Flights'],
        responses: [
            new OA\Response(response: 200, description: 'SSE stream of active flights'),
        ]
    )]
    public function stream(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            ob_implicit_flush(true);
            ob_end_flush();

            $lastHash = '';

            while (true) {
                if (connection_aborted()) break;

                $flights = ActiveFlight::active()->orderBy('position_updated_at', 'desc')->get()->toArray();
                $hash = md5(json_encode($flights));

                if ($hash !== $lastHash) {
                    echo "data: " . json_encode($flights) . "\n\n";
                    $lastHash = $hash;
                } else {
                    echo ": heartbeat\n\n";
                }

                if (ob_get_level() > 0) ob_flush();
                flush();

                sleep(2);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
