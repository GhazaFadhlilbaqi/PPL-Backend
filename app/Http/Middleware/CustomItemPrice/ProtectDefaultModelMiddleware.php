<?php

namespace App\Http\Middleware\CustomItemPrice;

use Closure;
use Illuminate\Http\Request;

class ProtectDefaultModelMiddleware
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
        if ($request->customItemPrice->is_default) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Can\'t edit default model !'
            ], 400);
        }

        return $next($request);
    }
}
