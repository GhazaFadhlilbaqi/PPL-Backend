<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AhpRequest;
use App\Models\Ahp;
use Illuminate\Http\Request;

class AhpController extends Controller
{

    protected $defaultAhpVariables = ['Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'p', 'pbb', 'ppl', 'pbk', 'ppp', 'm', 'n'];

    public function index()
    {
        $ahps = Ahp::all();

        $ahps = $ahps->map(function($ahp) {
            return $this->countAhpItem($ahp);
        });

        return response()->json([
            'status' => 'success',
            'data' => $ahps
        ]);
    }

    public function store(AhpRequest $request)
    {
        $ahp = Ahp::create($request->only(['id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('ahp')
        ]);
    }

    public function destroy(Ahp $ahp)
    {
        $ahp->delete();
        return response()->json([
            'status' => 'success',
        ], 204);
    }

    public function update(Ahp $ahp, AhpRequest $request)
    {
        $ahp->update($request->only(array_merge(['id', 'name'], $this->defaultAhpVariables)));

        return response()->json([
            'status' => 'success',
            'data' => compact('ahp'),
        ]);
    }

    private function countAhpItem($ahp)
    {

        $i = $ahp->i;
        $A = $ahp->A;
        $B = $ahp->B;
        $W = $ahp->W;
        $C = $ahp->C;

        $p = ($W / 10000) / 100;

        /**
         * ---------------------------------------
         * Hitung Biaya Pasti per Jam Kerja
         * ---------------------------------------
         * */

        # Hitung nilai sisa alat (C)
        $C = 0.1 * $B;

        # Hitung faktor angsuran modal (D)
        $D = (pow(1 + $i, $A) - 1) == 0 ? 0 : (($i / 100) * pow(1 + ($i / 100), $A)) / ((pow(1 + ($i / 100), $A)) - 1);

        # Hitung biaya pengembalian modal (E)
        $E = $W == 0 ? 0 : (($B - $C) * $D) / $W;

        # Hitung Asuransi dan lain lain (F)
        $F = $p == 0 ? 0 : ($p * $B / $W);

        # Hitung biaya pasti per jam (G)
        $G = $E + $F;

        /**
         * ---------------------------------------
         * Hitung Biaya Operasi per Jam Kerja
         * ---------------------------------------
         * */

        $Pw = $ahp->Pw;
        $Ms = $ahp->Ms;
        $Mp = $ahp->Mp;
        $U1 = $ahp->U1;
        $U2 = $ahp->U2;
        $pbb = $ahp->pbb;
        $ppl = $ahp->ppl;
        $pbk = $ahp->pbk;
        $ppp = $ahp->ppp;
        $m = $ahp->m;
        $n = $ahp->n;

        # Hitung Bahan Bakar (H)
        $H = ($pbb / 100) * $Pw * $Ms;

        # Hitung Pelumas (I)
        $I = ($ppl / 100) * $Pw * $Mp;

        # Hitung Biaya Bengkel (J)
        $J = $W == 0 ? 0 : (($pbk / 100) * $B) / $W;

        # Perawatan dan Perbaikan (K)
        $K = $W == 0 ? 0 : (($ppp / 100) * $B) / $W;

        # Operator (L)
        $L = $m * $U1;

        # Pembantu Operator (M)
        $M = $n * $U2;

        # Biaya Operasi per Jam (P)
        $P = $H + $I + $K + $L + $M;

        # Total Biaya Sewa Alat / Jam (S)
        $S = $G + $P;

        $ahp->C = $C;
        $ahp->D = $D;
        $ahp->E = $E;
        $ahp->F = $F;
        $ahp->G = $G;
        $ahp->H = $H;
        $ahp->I = $I;
        $ahp->J = $J;
        $ahp->K = $K;
        $ahp->L = $L;
        $ahp->M = $M;
        $ahp->P = $P;
        $ahp->S = $S;
        $ahp->p = $p;

        return $ahp;
    }
}
