<?php

namespace App\Http\Middleware\Project;

use Closure;
use Illuminate\Http\Request;

class EnsureProjectEligibleToExportMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // TODO: Should be query to database to check if user already pay for export
        $isBayar = true;

        if (!$isBayar) return abort(400, 'You can\'t download this file right now, please try again later');

        return $next($request);
    }
}
