<?php

namespace App\Http\Controllers;

use App\Models\Ahp;
use App\Models\Ahs;
use App\Models\CustomAhp;
use App\Models\CustomAhs;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomAhpController extends CountableItemController
{

    protected $defaultAhpVariables = ['Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'pbb', 'ppl', 'pbk', 'ppp', 'm', 'n'];

    # FIXME: Make below's method focused on fetching custom ahp only ! not mixed, or call it with different method
    public function index(Project $project)
    {
        $customAhps = $project->customAhp->map(function($data) {
            return $this->countAhpItem($data);
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

        # Check if user choose to copy from master
        if ($request->has('selected_reference') && $request->selected_reference) {

            return $this->copyAhpFromMaster($request, $project->hashidToId($project->hashid), explode('~', $request->selected_reference)[0]);

        } else {

            $ahp = CustomAhp::create($request->only(['code', 'name', 'project_id']));

            return response()->json([
                'status' => 'success',
                'data' => compact('ahp')
            ]);
        }

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

        $deps = $this->getCustomAhpDependencies($project->hashidToId($project->hashid), $customAhp->id);
        $hasDependencies = $deps['ahs']->count() > 0;

        if ($hasDependencies) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Data AHP ini masih terhubung dengan data lain !'
            ], 400);
        }

        $customAhp->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }

    private function copyAhpFromMaster(Request $request, $projectId, $referenceAhpId)
    {
        $referenceAhp = Ahp::find($referenceAhpId);

        if ($referenceAhp) {

            $ahp = CustomAhp::create(array_merge(
                [
                    'name' => $request->name,
                    'code' => $request->code,
                    'project_id' => $projectId,
                    'is_default' => false,
                ],
                $referenceAhp->select($this->defaultAhpVariables)->first()->toArray()
            ));

            return response()->json([
                'status' => 'success',
                'data' => compact('ahp')
            ]);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'Reference AHP not found'
            ], 400);

        }
    }

    private function getCustomAhpDependencies($projectId, $customAhpId)
    {
        $ahsDeps = CustomAhs::where('project_id', $projectId)->whereHas('customAhsItem', function($q) use ($customAhpId) {
            $q->where('custom_ahs_itemable_type', CustomAhp::class)->where('custom_ahs_itemable_id', $customAhpId);
        })->get();

        return [
            'ahs' => $ahsDeps
        ];
    }
}
