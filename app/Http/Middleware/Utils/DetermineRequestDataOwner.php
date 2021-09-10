<?php

namespace App\Http\Middleware\Utils;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetermineRequestDataOwner
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
        //NOTE: We agree that user id coming from request is should named as user_id
        $request->merge([
            'owner' => $request->has('user_id') ? User::findByHashid($request->user_id) : Auth::user(),
        ]);

        return $next($request);
    }
}
