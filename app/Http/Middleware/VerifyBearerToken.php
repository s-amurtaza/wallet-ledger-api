<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBearerToken
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $expectedToken = (string) env('API_TOKEN', '');

        $providedToken = (string) $request->bearerToken();

        if (
            $expectedToken === '' ||
            $providedToken === '' ||
            ! hash_equals($expectedToken, $providedToken)
        ) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Invalid or missing bearer token',
            ], 401);
        }

        return $next($request);
    }
}
