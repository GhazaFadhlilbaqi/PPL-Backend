<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItemHeader;
use Illuminate\Http\Request;

class RabItemHeaderController extends Controller
{
    public function index(Project $project, Rab $rab)
    {
        $itemHeaders = $rab->rabItemHeader;

        return response()->json([
            'status' => 'success',
            'data' => compact('itemHeaders')
        ]);
    }

    public function store(Project $project, Rab $rab, Request $request)
    {

        $request->merge(['rab_id' => $rab->hashidToId($rab->hashid)]);

        $rabItemHeader = RabItemHeader::create($request->only([
            'rab_id', 'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItemHeader')
        ]);
    }

    public function update(Project $project, Rab $rab, RabItemHeader $rabItemHeader, Request $request)
    {
        $rabItemHeader->update($request->only([
            'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItemHeader')
        ]);
    }

    public function destroy(Project $project, Rab $rab, RabItemHeader $rabItemHeader)
    {
        $rabItemHeader->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }
}
