<?php

namespace App\Http\Middleware\Project;

use App\Models\Project;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class ProjectSubscriptionLimitationGuard
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

        if ($request->project->activeOrder) {
            if (Carbon::parse($request->project->activeOrder->expired_at)->startOfDay()->lt(Carbon::now()->startOfDay())) {
                return response()->json([
                    'message' => 'Project expired. Mohon perpanjang / upgrade project untuk melanjutkan project ini.'
                ], 400);
            }
        }

        return $next($request);
    }
}
