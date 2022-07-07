<?php

namespace App\Http\Middleware\Project;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProjectBelongingMiddleware
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
        if ($request->project) {
            if ($request->project->user_id != Auth::user()->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'This project is not belongs to this user'
                ], 403);
            }
        }

        return $next($request);
    }
}
