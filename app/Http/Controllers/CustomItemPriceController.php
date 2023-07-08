<?php

namespace App\Http\Controllers;

use App\Models\Ahs;
use App\Models\CustomAhs;
use App\Models\CustomItemPrice;
use App\Models\CustomItemPriceGroup;
use App\Models\ItemPriceGroup;
use App\Models\Project;
use App\Traits\UnitTrait;
use Carbon\Carbon;
use Exception;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;

class CustomItemPriceController extends CustomItemPriceBaseController
{

    use UnitTrait;

    public function index(Project $project)
    {

        $itemPrices = $project->customItemPrice;

        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrices')
        ]);
    }

    # This method should only used for creating a custom item price only, not for creating referenced custom item price !
    public function store(Project $project, Request $request)
    {

        $request->merge([
            'custom_item_price_group_id' => Hashids::decode($request->custom_item_price_group_id)[0],
            'unit_id' => $request->has('unit_id') ? Hashids::decode($request->unit_id)[0] : $this->getFirstUnit()->id,
            'project_id' => Hashids::decode($request->project_id)[0]
        ]);

        if ($request->has('unique_check') && $request->unique_check == 'true') {
            $customItemPrice = CustomItemPrice::where('project_id', $request->project_id)->where('code', $request->code)->first();
            if ($customItemPrice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode ' . $request->code . ' telah terpakai'
                ], 422);
            }
        }

        # TODO: Implement unique validation check for code field unique to item price and custom item price table table
        $customItemPrice = CustomItemPrice::create($request->only([
            'code', 'custom_item_price_group_id', 'unit_id', 'project_id', 'name', 'price'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPrice'),
        ]);
    }

    public function destroy(Project $project, CustomItemPrice $customItemPrice)
    {
        // TODO: Check if there are any childerns used this custom item price

        $customItemPriceDeps = $this->getCustomItemPriceDependencies($project->hashidToId($project->hashid), $customItemPrice->id);
        $hasDependencies = $customItemPriceDeps['ahs']->count() > 0;

        if ($hasDependencies) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Harga Satuan ini masih terhubung dengan data lain !'
            ], 400);
        }

        if ($customItemPrice->is_default) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Default item price can\'t be deleted !',
            ], 400);
        }

        $customItemPrice = $customItemPrice->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function update(Project $project, CustomItemPrice $customItemPrice, Request $request)
    {
        // TODO: Add validation : if id changed, all the children should changed too
        try {

            $unsets = collect(['code', 'name', 'unit_id']);

            if ($customItemPrice->is_default) {
                $unsets->each(function($data) use ($request) { $request->offsetUnset($data); });
            }

            if ($request->code) {

                // FIXME: Move me to request validation
                $existedItemPrice = CustomItemPrice::where('project_id', $project->hashidToId($project->hashid))->where('id', '!=', $customItemPrice->hashidToId($customItemPrice->hashid))->where('code', $request->code)->get();

                if ($existedItemPrice->count() > 0) {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'This code is not available',
                    ], 400);
                }
            }

            if ($request->has('unit_id') && $request->unit_id) {
                $request->merge([
                    'unit_id' => Hashids::decode($request->unit_id)[0]
                ]);
            }

            $customItemPrice->update($request->only([
                'code', 'unit_id', 'name', 'price'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Data updated'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function copyFromMasterItemPrice($projectId)
    {

        $project = Project::find($projectId);

        // Copy all master item price data
        $itemPriceGroups = ItemPriceGroup::with(['itemPrice' => function($data) use ($project) {
            $data->with(['price' => function($q) use ($project) {
                $q->where('province_id', $project->province_id);
            }]);
        }])->get();

        $customItemPrices = [];

        foreach ($itemPriceGroups as $itemPriceGroup) {

            $customItemPriceGroup = CustomItemPriceGroup::create([
                'project_id' => $projectId,
                'is_default' => true,
                'name' => $itemPriceGroup->name,
            ]);

            $customItemPrices = CustomItemPrice::insert($itemPriceGroup->itemPrice->map(function($itemPriceMaster) use ($projectId, $customItemPriceGroup) {
                return [
                    'code' => $itemPriceMaster->id,
                    'custom_item_price_group_id' => $customItemPriceGroup->id,
                    'unit_id' => $itemPriceMaster->unit_id,
                    'project_id' => $projectId,
                    'name' => $itemPriceMaster->name,
                    'is_default' => true,
                    'price' => count($itemPriceMaster->price) > 0 ? $itemPriceMaster->price[0]->price : 0,
                    'default_price' => count($itemPriceMaster->price) > 0 ? $itemPriceMaster->price[0]->price : 0,
                    'created_at' => Carbon::now()
                ];
            })->toArray());
        }

        return $customItemPrices;
    }
}
