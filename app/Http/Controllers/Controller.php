<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Vuetable\Vuetable;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get Formatted Table
     *
     * @param mixed $model
     * @return CollectionVuetableBuilder|EloquentVuetableBuilder
     */
    protected function getTableFormattedData($model)
    {
        return Vuetable::of($model);
    }
}
