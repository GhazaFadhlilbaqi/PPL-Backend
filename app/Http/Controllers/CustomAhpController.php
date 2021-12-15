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

    // FIXME: Using validation request
    public function store(Project $project, Request $request)
    {

        $isCustomAhpExist = CustomAhp::where('project_id', $project->hashidToId($project->hashid))->where('code', $request->id)->count() > 0;

        if ($isCustomAhpExist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Custom AHP is exist !',
            ], 422);
        }

        $request->merge([
            'project_id' =>$project->hashidToId($project->hashid),
        ]);

        $customAhp = CustomAhp::create($request->only(['project_id', 'code', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhp')
        ]);
    }

    // FIXME: Using validation request
    public function update(Project $project, CustomAhp $customAhp, Request $request)
    {

        // NOTE: We don't need to verify to it's children when deleting ahp because the children should referenced to it's ID, not code
        $customAhp->update($request->only(array_merge(['code', 'name'], $this->defaultAhpVariables)));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhp')
        ]);
    }

    public function destroy(Project $project, CustomAhp $customAhp)
    {
        // TODO: Delete it's dependencies
        $customAhp->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }
}
