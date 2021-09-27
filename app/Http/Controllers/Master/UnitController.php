<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;
use Exception;

class UnitController extends Controller
{

    public function index(Request $request)
    {

        if ($request->has('datatable') && $request->datatable == 'false') {

            $units = Unit::all();

            return response()->json([
                'status' => 'success',
                'data' => compact('units'),
            ]);
        }

        return $this->getTableFormattedData(Unit::query())->make();
    }

    public function store(UnitRequest $request)
    {
        $unit = Unit::create($request->only(['name']));
        return response()->json([
            'status' => 'success',
            'data' => compact('unit')
        ]);
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

    public function update(UnitRequest $request, Unit $unit)
    {
        $unit = $unit->update($request->only(['name']));
        return response()->json([
            'status' => 'success',
            'data' => compact('unit'),
        ]);
    }
}
