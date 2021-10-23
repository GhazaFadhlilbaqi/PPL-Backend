<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AhpRequest;
use App\Models\Ahp;
use Illuminate\Http\Request;

class AhpController extends Controller
{
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
        $ahp->update($request->only([
            'Pw', 'Cp', 'A', 'W', 'B', 'i', 'U1', 'U2', 'Mb', 'Ms', 'Mp', 'id', 'name'
        ]));

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
        $F = $W == 0 ? 0 : (($W / 1000000) * $B) / $W;

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

        # Hitung Bahan Bakar (H)
        $H = ((0.1 + 0.175) / 2) * $Pw * $Ms;

        # Hitung Pelumas (I)
        $I = (0.01 * $Pw) * $Mp;

        # Perawatan dan Perbaikan (K)
        $K = $W == 0 ? 0 : ((17.5 / 100) * $B) / $W;

        # Operator (L)
        $L = 1 * $U1;

        # Pembantu Operator (M)
        $M = 0 * $U2;

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
        $ahp->K = $K;
        $ahp->L = $L;
        $ahp->M = $M;
        $ahp->P = $P;
        $ahp->S = $S;

        return $ahp;
    }
}
