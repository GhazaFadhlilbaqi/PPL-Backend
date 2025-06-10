<?php

namespace App\Http\Controllers;

use App\Http\Resources\AhsReferenceGroupResource;
use App\Models\AhsReferenceGroup;
use App\Models\Project;

class AhsReferenceGroupController extends CountableItemController
{
    public function index()
    {
      $ahsReferenceGroups = AhsReferenceGroup::whereNull('deleted_at')->get();
      return response()->json([
          'status' => 'success',
          'data' => AhsReferenceGroupResource::collection($ahsReferenceGroups)
      ]);
    }
}
