<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\CountableItemController;
use App\Http\Requests\AhsRequest;
use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\Province;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;

class AhsController extends CountableItemController
{
    public function index(Request $request, $ahsId = null)
    {
        $ahs = !is_null($ahsId) ? Ahs::where('id', $ahsId) : Ahs::query();
        $ahs = $ahs->with(['ahsItem' => function($ahsItem) { $ahsItem->with(['ahsItemable', 'unit']); }])->orderBy('created_at', 'ASC');
        $isPaginateRequest = $request->has('page') && (int) $request->page > 0;
        $paginationAttribute = [];

        # Paginate AHS
        if ($isPaginateRequest) {
            $paginationResult = $this->paginateAhs($ahs, $request->page);
            $ahs = $paginationResult['ahs'];
            $paginationAttribute['total_page'] = $paginationResult['total_page'];
        };

        $ahs = $ahs->get();

        $provinceId = Hashids::decode($request->province);

        # Categorizing by section
        if ($request->arrange == 'true' && $request->has('province')) {

            $itemArranged = [];

            foreach ($ahs as $key => $a) {

                # Categorizing by it's section. (e.g labor, ingredients, etc)
                foreach ($a->ahsItem as $key2 => $aItem) $itemArranged[$aItem->section][] = $aItem;

                $ahs[$key]['item_arranged'] = $itemArranged;

                $itemArranged = [];
                $ahs[$key] = $this->countAhsSubtotal($a, $provinceId);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'ahs' => $ahs,
                'pagination_attribute' => $paginationAttribute,
            ]
        ]);
    }

    public function getAhsIds()
    {

        // FIXME: Definiteluy need more improovement !
        $ahses = Ahs::all();
        $arrayAhses = [];

        $ahses = $ahses->filter(function($ahs) {
            foreach ($ahs->ahsItem as $ahsItem) {
                if ($ahsItem->ahs_itemable_type == Ahs::class) return false;
            }
            return true;
        });

        $ahses = $ahses->map(function($ahs) {
            return [
                'name' => $ahs->name,
                'code' => $ahs->code,
                'id' => $ahs->id,
            ];
        });

        foreach ($ahses as $ahs) {
            $arrayAhses[] = $ahs;
        }

        $ahses = $arrayAhses;

        return response()->json([
            'status' => 'success',
            'data' => compact('ahses')
        ]);
    }

    public function store(AhsRequest $request)
    {
        Ahs::create($request->only(['id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => Ahs::all()
        ]);
    }

    public function destroy(Ahs $ahs)
    {

        # Check if there are any ahs depends on this ahs, prevent to delete !
        $dependantsAhsItem = AhsItem::where('ahs_itemable_id', $ahs->id);

        if ($dependantsAhsItem->count()) {
            # FIXME: Make better error handler
            return response()->json([
                'status' => 'fail',
                'message' => 'Masih ada item ahs lain yang bergantung dengan AHS ini !',
            ], 400);
        }

        $ahs->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'AHS Deleted'
        ], 204);
    }

    public function update(AhsRequest $request, Ahs $ahs)
    {

        if ($request->has('id') && ($ahs->id != $request->id)) {
            $oldId = $ahs->id;
        }

        $ahs->update($request->only([
            'id', 'name'
        ]));

        AhsItem::where('ahs_itemable_id', $oldId)->update([
            'ahs_itemable_id' => $request->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => compact('ahs')
        ]);
    }

    private function paginateAhs($ahs, $currentPage)
    {
        $ahsPerPage = 10;
        $totalPage = ceil($ahs->count() / $ahsPerPage);
        $currentIndexStart = ($ahsPerPage * (int) $currentPage) - $ahsPerPage;

        $ahs = $ahs->skip($currentIndexStart)->take($ahsPerPage);

        return [
            'total_page' => $totalPage,
            'current_page' => $currentPage,
            'current_index_range' => [$currentIndexStart, $currentIndexStart + $ahsPerPage],
            'ahs' => $ahs
        ];
    }
}
