<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CommonController extends Controller
{
    #[OA\Get(
        path: '/api/airports',
        summary: 'List all airports',
        description: 'Returns a list of all registered airports in the system.',
        tags: ['Common'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of airports',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Airport'))
            ),
        ]
    )]
    public function airports(): JsonResponse
    {
        return response()->json(Airport::all());
    }
}
