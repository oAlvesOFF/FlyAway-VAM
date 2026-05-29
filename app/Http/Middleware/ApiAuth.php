<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized — missing token'], 401);
        }

        // Find user by their personal api_key
        $user = User::where('api_key', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized — invalid token'], 401);
        }

        Auth::login($user);
        return $next($request);
    }
}
