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

    }

    public function update(Project $project, CustomAhs $customAhs, CustomAhsRequest $request)
    {

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
        $customAhs = CustomAhs::create($request->only([
            'name', 'id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhs')
        ]);
    }
}
