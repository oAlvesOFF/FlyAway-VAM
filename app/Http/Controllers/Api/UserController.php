<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/api/user',
        summary: 'Get current user',
        description: 'Returns the authenticated user\'s profile including rank, bids, and aircraft.',
        tags: ['User'],
        security: [['apiAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile retrieved',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function me(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not found — use a personal API key'], 404);
        }
        return response()->json($user->load(['rank:id,name,image,minimum_hours', 'bids.schedule', 'bids.aircraft']));
    }
}
