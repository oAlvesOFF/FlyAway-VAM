<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FlightController extends Controller
{
    #[OA\Get(path: '/api/aircraft', summary: 'List aircraft', description: 'Returns all registered aircraft in the fleet. Requires API authentication.', tags: ['Aircraft'], security: [['apiAuth' => []]], responses: [
        new OA\Response(response: 200, description: 'List of aircraft', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Aircraft'))),
    ])]
    public function aircraft(): JsonResponse
    {
        return response()->json(Aircraft::all()->makeVisible(['total_hours_since_service']));
    }

    #[OA\Get(path: '/api/schedules', summary: 'List schedules', description: 'Returns all flight schedules. Requires API authentication.', tags: ['Schedules'], security: [['apiAuth' => []]], responses: [
        new OA\Response(response: 200, description: 'List of schedules', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Schedule'))),
    ])]
    public function schedules(): JsonResponse
    {
        return response()->json(Schedule::all());
    }

    #[OA\Get(path: '/api/schedules/my-reservations', summary: 'Get my reservations', description: 'Returns all flight reservations for the authenticated pilot. Requires API authentication.', tags: ['Schedules'], security: [['apiAuth' => []]], responses: [
        new OA\Response(response: 200, description: 'List of reservations', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Bid'))),
    ])]
    public function myReservations(Request $request): JsonResponse
    {
        $reservations = Bid::with(['schedule', 'aircraft'])
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($reservations);
    }
}
