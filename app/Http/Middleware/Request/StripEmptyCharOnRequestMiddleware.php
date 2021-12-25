<?php

namespace App\Http\Middleware\Request;

use Closure;
use Illuminate\Http\Request;

class StripEmptyCharOnRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$fields)
    {

        foreach ($request->all() as $key => $req) {
            if (in_array($key, $fields)) {
                if ($req == '') $request->offsetUnset($key);
            }
        }

        return $next($request);
    }
}
