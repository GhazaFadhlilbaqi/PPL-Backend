<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function store(UnitRequest $request)
    {
        $unit = Unit::create($request->only(['name']));
        return response()->json([
            'status' => 'success',
            'data' => compact('unit')
        ]);
    }
}
