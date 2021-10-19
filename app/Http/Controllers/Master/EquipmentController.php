<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        // if ($request->has('datatable') && $request->datatable == 'false') {

        // }
        return $this->getTableFormattedData(Equipment::query())->make();
    }

    public function store(EquipmentRequest $request)
    {
        $equipment = Equipment::create($request->only(['id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('equipment')
        ]);
    }
}
