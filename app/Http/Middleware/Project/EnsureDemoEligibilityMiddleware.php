<?php

namespace App\Http\Middleware\Project;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDemoEligibilityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('subscription_id') && $request->subscription_id == 'demo') {
            if (Auth::user()->demo_quota <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk menggunakan kuota demo. Alasan : Kuota demo anda telah habis'
                ], 400);
            }
        }

        return $next($request);
    }
}
