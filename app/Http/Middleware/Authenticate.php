<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return abort(403, 'You are not authenticated yet !');
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'You must authenticated first before accessing this route !'
            ]);
        }
    }
}
