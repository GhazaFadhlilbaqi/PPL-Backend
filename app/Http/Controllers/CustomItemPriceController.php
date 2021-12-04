<?php

namespace App\Http\Controllers;

use App\Models\CustomItemPrice;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\Project;
use App\Models\Unit;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;

class CustomItemPriceController extends Controller
{
    public function index(Project $project)
    {

        $masterItemPrices = ItemPrice::all();
        $customItemPrices = $project->customItemPrice;

        $itemPrices = $this->getCustomItemPrices($project, $customItemPrices);

        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrices')
        ]);
    }

    # This method should only used for creating a custom item price only, not for creating referenced custom item price !
    public function store(Project $project, Request $request)
    {

        $dataToMerge = [];

        if ($request->has('custom_item_priceable_id')) $dataToMerge['custom_item_priceable_id'] = Hashids::decode($request->custom_item_priceable_id)[0];

        $dataToMerge['unit_id'] = Unit::first()->id;
        $dataToMerge['project_id'] = $project->hashidToId($project->hashid);
        $dataToMerge['custom_item_priceable_type'] = 'App\\Models\\' . $request->custom_item_priceable_type;

        $request->merge($dataToMerge);

        # TODO: Implement unique validation check for code field unique to item price and custom item price table table
        $customItemPrice = CustomItemPrice::create($request->only([
            'code', 'custom_item_priceable_id', 'custom_item_priceable_type', 'unit_id', 'project_id', 'name', 'price'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPrice'),
        ]);
    }

    public function destroy(Project $project, $abstractItemPriceId)
    {
        // TODO: Check if there are any childerns used this custom item price
        $customItemPrice = CustomItemPrice::where('project', $project->hashidToId($project->hashid))->where('code', $abstractItemPriceId)->first();
        $customItemPrice->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function update(Project $project, Request $request, $abstractItemPriceId)
    {

        $projectId = $project->hashidToId($project->hashid);
        $referencedItem = ItemPrice::withPriceByProvince(Hashids::decode($project->hashed_province_id)[0])->where('id', $abstractItemPriceId)->first();

        if ($referencedItem) {

            $referencedItemPrice = $referencedItem->price[0]->price;

            $currentCustomItemPriceQuery = CustomItemPrice::where('project_id', $projectId)->where('code', $abstractItemPriceId);
            $currentCustomItemPrice = $currentCustomItemPriceQuery->first();

            if ($currentCustomItemPrice && $referencedItemPrice == $request->price) {

                $currentCustomItemPriceQuery->delete();

            } else if ($currentCustomItemPrice && $referencedItemPrice != $request->price) {

                $currentCustomItemPrice->price = $request->price;
                $currentCustomItemPrice->save();

            } else if (!$currentCustomItemPrice && $referencedItemPrice != $request->price) {

                $itemPrice = CustomItemPrice::create([
                    'code' => $referencedItem->id,
                    'custom_item_priceable_id' => $referencedItem->itemPriceGroup->id,
                    'custom_item_priceable_type' => ItemPriceGroup::class,
                    'unit_id' => $referencedItem->unit_id,
                    'project_id' => $projectId,
                    'name' => $referencedItem->name,
                    'price' => $request->price,
                ]);

            }

        } else {

            $itemPrice = CustomItemPrice::where('project_id', $projectId)->where('code', $abstractItemPriceId)->first();

            if (!$itemPrice) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'No custom item price match with given criteria'
                ], 400);
            }

            $itemPrice->update($request->only([
                'code', 'custom_item_priceable_id', 'custom_item_priceable_type', 'unit_id', 'name', 'price'
            ]));

        }

        return response()->json([
            'status' => 'success',
            'message' => 'updated'
        ]);
    }

    # Comparing custom item price with master item price
    private function getCustomItemPrices($project, $customItemPrices)
    {

        $customItemPricesAvail = $customItemPrices->map(function ($customItemPrice, $index) use ($customItemPrices) {
            $price = $customItemPrice->price;
            unset($customItemPrices[$index]->price);
            $customItemPrices[$index]->price = $price;
            return $customItemPrice->code;
        });

        $itemPrices = ItemPrice::withPriceByProvince($project->province_id)->whereNotIn('id', $customItemPricesAvail)->get();
        $itemPrices = $itemPrices->map(function ($data) {
            if (count($data->price)) {
                $price = $data->price[0]->price;
                unset($data->price);
                $data->price = $price;
            } else {
                unset($data->price);
                $data->price = 0;
            }
            return $data;
        });

        $itemPrices = $itemPrices->merge($customItemPrices);

        return $itemPrices;
    }
}
