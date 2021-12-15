<?php

namespace App\Traits\Middleware;

use Closure;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Model;

trait ProtectDefaultModelTrait {

    protected function blockDefaultModelAction(Model $model, Request $request, Closure $next)
    {

    }

}
