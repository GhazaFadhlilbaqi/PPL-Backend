<?php

namespace App\Traits;

use App\Models\Unit;

trait UnitTrait {

    protected function getFirstUnit()
    {
        return Unit::first();
    }

}
