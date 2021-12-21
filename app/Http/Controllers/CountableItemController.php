<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemPrice;
use App\Models\Ahp;
use App\Models\Ahs;
use App\Models\CustomAhp;
use App\Models\CustomAhs;
use App\Models\CustomItemPrice;
use App\Models\ItemPriceProvince;
use App\Models\Province;
use Exception;

class CountableItemController extends Controller
{
    protected function countAhpItem($ahp)
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
        $ahp->subtotal = $S;

        return $ahp;
    }

    # Count price per item + subtotal (by multiply of price per item and coefficient)
    protected function countAhsItemTotal($ahsItem, $province = null)
    {

        # Check if ahsItem referenced to item price
        if ($ahsItem->ahs_itemable_type === ItemPrice::class) {

            // $itemPrice = $ahsItem->ahsItemable->with(['price' => function($q) use ($province, $ahsItem) {
            //     $q->where('province_id', $province);
            // }])->first();

            // HACK: This is a shortcut to get accurate price by province, but it's take 1 query more
            $itemPrice = ItemPriceProvince::where('province_id', $province)->where('item_price_id', $ahsItem->ahs_itemable_id)->first();

            // $fixedPrice = count($itemPrice->price) > 0 ? $itemPrice->price[0]->price : 0;
            $fixedPrice = $itemPrice ? ($itemPrice->price ?? 0) : 0;
            $ahsItem->ahsItemable->subtotal = $fixedPrice;

            return $fixedPrice * $ahsItem->coefficient;

        } else if ($ahsItem->ahs_itemable_type === Ahs::class) {
            return $this->countAhsSubtotal($ahsItem->ahsItemable, $province)->subtotal * $ahsItem->coefficient;
        } else if ($ahsItem->ahs_itemable_type === Ahp::class) {
            return $this->countAhpItem($ahsItem->ahsItemable)->S * $ahsItem->coefficient;
        } else {
            throw new Exception('Itemable type not supported');
        }
    }

    protected function countAhsSubtotal($ahs, $province = null)
    {
        $ahsSubtotal = 0;

        foreach ($ahs->ahsItem as $ahsItem) {
            $ahsItem->subtotal = $this->countAhsItemTotal($ahsItem, $province);
            $ahsSubtotal += $ahsItem->subtotal;
        }

        $ahs->subtotal = $ahsSubtotal;

        return $ahs;

    }

    // FIXME: Definitely need more improovement
    protected function countCustomAhsItemTotal($customAhsItem)
    {

        # Check if ahsItem referenced to item price
        if ($customAhsItem->custom_ahs_itemable_type === CustomItemPrice::class) {

            $fixedPrice = $customAhsItem->customAhsItemable->price ?? 0;
            $customAhsItem->customAhsItemable->subtotal = $fixedPrice;

            return $fixedPrice * $customAhsItem->coefficient;

        } else if ($customAhsItem->custom_ahs_itemable_type === CustomAhs::class) {

            return $this->countCustomAhsSubtotal($customAhsItem->customAhsItemable)->subtotal * $customAhsItem->coefficient;

        } else if ($customAhsItem->custom_ahs_itemable_type === CustomAhp::class) {

            return $this->countAhpItem($customAhsItem->customAhsItemable)->S * $customAhsItem->coefficient;

        } else {

            throw new Exception('Itemable type not supported');

        }
    }

    protected function countCustomAhsSubtotal($customAhs, $province = null)
    {
        $ahsSubtotal = 0;

        foreach ($customAhs->customAhsItem as $customAhsItem) {
            $customAhsItem->subtotal = $this->countCustomAhsItemTotal($customAhsItem);
            $ahsSubtotal += $customAhsItem->subtotal;
        }

        $customAhs->subtotal = $ahsSubtotal;

        return $customAhs;

    }
}
