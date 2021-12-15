<?php

namespace App\Traits;

use App\Models\CustomAhp;
use App\Models\Ahp;
use Carbon\Carbon;

trait CustomAhpTrait {

    protected function copyAhpFromMaster($projectId)
    {

        $defaultAhpVariables = ['Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'pbb', 'ppl', 'pbk', 'ppp', 'm', 'n'];

        $ahps = Ahp::all();
        $insertedAhpId = [];

        foreach ($ahps as $ahp) {
            $insertedAhpId[] = array_merge([
                'project_id' => $projectId,
                'code' => $ahp->id,
                'created_at' => Carbon::now(),
                'name' => $ahp->name,
                'is_default' => true,
            ], $ahp->select($defaultAhpVariables)->first()->toArray());
        }

        $data = CustomAhp::insert($insertedAhpId);
        return response()->json(['d' => $data]);
    }
}
