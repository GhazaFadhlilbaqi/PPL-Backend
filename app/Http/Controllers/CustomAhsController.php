<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomAhsRequest;
use App\Models\CustomAhs;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomAhsController extends Controller
{
    public function index(Project $project)
    {
        $customAhs = $project->with(['rab' => function($q) {
            $q->with('customAhs');
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => $customAhs,
        ]);
    }

    public function update(Project $project, CustomAhs $customAhs, CustomAhsRequest $request)
    {
        $customAhs->update($request->only([
            'id', 'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhs')
        ]);
    }

    public function destroy(Project $project, CustomAhs $customAhs)
    {
        $customAhs->delete();
        // TODO: Implement delete AHS Item and it's relation
        return response()->json([
            'status' => 'success',
        ], 204);
    }

    public function store(Project $project, CustomAhsRequest $request)
    {

        return $project;

        // $request->merge(['rab_id' => 2]);

        return response()->json(['data' => 'testing']);

        $customAhs = CustomAhs::create($request->only([
            'name', 'code', 'rab_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhs')
        ]);
    }
}
