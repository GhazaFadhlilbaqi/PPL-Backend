<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProtectDebugMiddleware
{
    /**
     * This middleware prevent debug feature accessed during non debug mode
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!env('APP_DEBUG')) {
            return response()->json([
                'success' => false,
                'message' => 'The page you requested is not found.'
            ], 404);
        }

        return $next($request);
    }
}
