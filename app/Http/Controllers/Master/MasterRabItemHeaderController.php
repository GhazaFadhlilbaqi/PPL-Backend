<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterRab;
use App\Models\MasterRabItemHeader;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class MasterRabItemHeaderController extends Controller
{
    public function index(MasterRab $masterRab)
    {
        $itemHeaders = $masterRab->rabItemHeader;

        return response()->json([
            'status' => 'success',
            'data' => compact('itemHeaders')
        ]);
    }

    public function store(MasterRab $masterRab, Request $request)
    {

        $request->merge(['master_rab_id' => $masterRab->hashidToId($masterRab->hashid)]);

        $masterRabItemHeader = MasterRabItemHeader::create($request->only([
            'master_rab_id', 'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('masterRabItemHeader')
        ]);
    }

    public function update(MasterRab $masterRab, MasterRabItemHeader $masterRabItemHeader, Request $request)
    {
        $masterRabItemHeader->update($request->only([
            'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('masterRabItemHeader')
        ]);
    }

    public function destroy(MasterRab $masterRab, MasterRabItemHeader $masterRabItemHeader)
    {
        $masterRabItemHeader->delete();
        return response()->json([
            'status' => 'success',
        ], 204);
    }
}
