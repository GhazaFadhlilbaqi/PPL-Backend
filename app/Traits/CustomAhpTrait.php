<?php

namespace App\Traits;

use App\Models\CustomAhp;
use App\Models\Ahp;
use Carbon\Carbon;

trait CustomAhpTrait {

    protected function copyAhpFromMaster($projectId)
    {
        // $defaultAhpVariables = ['Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'pbb', 'ppl', 'pbk', 'ppp', 'm', 'n'];
        $ahps = Ahp::all();
        $insertedAhpId = [];

        foreach ($ahps as $ahp) {
            $insertedAhpId[] = [
                'project_id' => $projectId,
                'code' => $ahp->id,
                'created_at' => Carbon::now(),
                'name' => $ahp->name,
                'is_default' => true,
                'Pw' => $ahp->Pw,
                'Cp' => $ahp->Cp,
                'A' => $ahp->A,
                'W' => $ahp->W,
                'B' => $ahp->B,
                'i' => $ahp->i,
                'U1' => $ahp->U1,
                'U2' => $ahp->U2,
                'Mb' => $ahp->Mb,
                'Ms' => $ahp->Ms,
                'Mp' => $ahp->Mp,
                'pbb' => $ahp->pbb,
                'ppl' => $ahp->ppl,
                'pbk' => $ahp->pbk,
                'ppp' => $ahp->ppp,
                'm' => $ahp->m,
                'n' => $ahp->n
            ];
        }

        $data = CustomAhp::insert($insertedAhpId);

        return response()->json(['d' => $data]);
    }
}
