<?php

namespace App\Http\Controllers;

use App\Models\Ahp;
use App\Models\CustomAhp;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomAhpController extends CountableItemController
{

    protected $defaultAhpVariables = ['Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'pbb', 'ppl', 'pbk', 'ppp', 'm', 'n'];

    # FIXME: Make below's method focused on fetching custom ahp only ! not mixed, or call it with different method
    public function index(Project $project)
    {
        $customAhps = $project->customAhp;
        $customAhps = $customAhps->merge(Ahp::all());
        $customAhps = $customAhps->map(function($customAhp) {
            return $this->countAhpItem($customAhp);
        });

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhps')
        ]);
    }

    public function store(Project $project, Request $request)
    {

        $isCustomAhpExist = CustomAhp::where('project_id', $project->hashidToId($project->hashid))->where('code', $request->id)->count() >= 0;

        if ($isCustomAhpExist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Custom AHP is exist !',
            ], 422);
        }

        $request->merge([
            'project_id' =>$project->hashidToId($project->hashid),
            'code' => $request->id,
        ]);

        $customAhp = CustomAhp::create($request->only(array_merge($this->defaultAhpVariables, ['project_id', 'code', 'name'])));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhp')
        ]);
    }
}
