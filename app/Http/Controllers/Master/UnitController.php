<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Exception;
use Illuminate\Http\Request;
use Vuetable\Vuetable;

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

    public function getAllData()
    {
        return Vuetable::of(Unit::query())->make();
    }

    public function destroy(Unit $unit)
    {
        try {
            $unit->delete();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
