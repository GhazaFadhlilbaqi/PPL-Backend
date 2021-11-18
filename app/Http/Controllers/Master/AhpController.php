<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\CountableItemController;
use App\Http\Requests\AhpRequest;
use App\Models\Ahp;
use App\Models\AhsItem;
use Illuminate\Http\Request;

class AhpController extends CountableItemController
{

    protected $defaultAhpVariables = ['Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'pbb', 'ppl', 'pbk', 'ppp', 'm', 'n'];

    public function index()
    {
        $ahps = Ahp::all();
        $ahps = $ahps->map(function($ahp) { return $this->countAhpItem($ahp); });

        return response()->json([
            'status' => 'success',
            'data' => $ahps
        ]);
    }

    public function store(AhpRequest $request)
    {
        $ahp = Ahp::create($request->only(['id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('ahp')
        ]);
    }

    public function destroy(Ahp $ahp)
    {
        $ahp->delete();
        return response()->json([
            'status' => 'success',
        ], 204);
    }

    public function update(Ahp $ahp, AhpRequest $request)
    {
        $idChanged = $request->has('id') && ($request->id != $ahp->id);

        if ($idChanged) $oldId = $ahp->id;

        $ahp->update($request->only(array_merge(['id', 'name'], $this->defaultAhpVariables)));

        if ($idChanged) AhsItem::where('ahs_itemable_id', $oldId)->update([
            'ahs_itemable_id' => $request->id
        ]);

        return response()->json([
            'status' => 'success',
            'data' => compact('ahp'),
        ]);
    }
}
