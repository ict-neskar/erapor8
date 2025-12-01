<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Sekolah;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    const AUTH_HEADER = 'X-Api-Key';
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header(self::AUTH_HEADER);
        $apiKey = Sekolah::where('sekolah_id', $header)->first();
        if ($apiKey instanceof Sekolah) {
            return $next($request);
        }
        return response()->json([
            'errors' => [[
                'message' => 'Unauthorized'
            ]]
        ], 401);
    }
}
