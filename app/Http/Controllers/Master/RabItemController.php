<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Ahs;
use App\Models\MasterRab;
use App\Models\MasterRabItem;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Unit;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class RabItemController extends Controller
{
    public function store(MasterRab $masterRab, Request $request)
    {
        $request->merge([
            'master_rab_id' => $masterRab->hashidToId($masterRab->hashid),
            'unit_id' => ($request->has('unit_id') && $request->master_rab_item_header_id)
              ? Unit::findByHashid($request->unit_id)->id
              : Unit::first()->id,
            'master_rab_item_header_id' => ($request->has('master_rab_item_header_id') && $request->master_rab_item_header_id)
              ? Hashids::decode($request->master_rab_item_header_id)[0]
              : NULL
        ]);

        $rabItem = MasterRabItem::create($request->only([
            'master_rab_id', 'name', 'ahs_id', 'volume', 'unit_id', 'master_rab_item_header_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItem'),
        ]);
    }

    public function update(MasterRab $masterRab, MasterRabItem $masterRabItem, Request $request)
    {

        $dataToMerge = [];

        if ($request->has('unit_id') && $request->unit_id) {
            $dataToMerge['unit_id'] = Hashids::decode($request->unit_id)[0];
        }



        // if ($request->has('ahs_id') && $request->ahs_id) {
        //     $dataToMerge['ahs_id'] = Hashids::decode($request->ahs_id)[0];
        // }

        if ($request->ahs_id && $request->ahs_id != null && $request->ahs_id != '-') {
            if ($request->ahs_id != $masterRabItem->ahs_id) {
                $ahsItem = Ahs::find($request->ahs_id);
                $request->merge([
                    'volume' => null,
                    'name' => $ahsItem->name
                ]);
                // $request->volume = null;
                // $request->name = $ahsItem->name;
            }
        } else {
            $request->merge([
                'ahs_id' => null,
            ]);
        }

        if (!$request->has('volume') || $request->volume == '') {
            $dataToMerge['volume'] = 0;
        }

        $request->merge($dataToMerge);

        $masterRabItem->update($request->only([
            'name', 'ahs_id', 'volume', 'unit_id', 'price'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('masterRabItem')
        ]);
    }

    public function destroy(MasterRab $masterRab, MasterRabItem $masterRabItem)
    {
        $masterRabItem->delete();

        return response()->json([
            'status' => 'success'
        ], 204);
    }
}
