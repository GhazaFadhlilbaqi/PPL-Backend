<?php

namespace App\Http\Controllers;

use App\Models\CustomItemPrice;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomItemPriceController extends Controller
{
    public function index(Project $project)
    {

        $masterItemPrices = ItemPrice::all();
        $customItemPrices = $project->customItemPrice;

        return response()->json([
            'data' => compact('masterItemPrices', 'customItemPrices'),
        ]);

        $itemPrices = $this->compareItemPrice($masterItemPrices, $customItemPrices);

        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrices')
        ]);
    }

    public function store(Project $project, Request $request)
    {

        # This method should only used for creating a custom item price only, not for creating referenced custom item price !
        $customItemPrice = CustomItemPrice::create($request->only([
            'code', 'custom_item_priceable_id', 'custom_item_priceable_type', 'unit_id', 'project_id', 'name', 'price'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPrice'),
        ]);
    }

    public function update(Project $project, Request $request, $abstractItemPriceId)
    {

        $referencedItemPrice = ItemPrice::find($abstractItemPriceId);
        $projectId = $project->hashidToId($project->hashid);

        if ($referencedItemPrice) {

            $itemPrice = CustomItemPrice::create([
                'code' => $referencedItemPrice->id,
                'custom_item_priceable_id' => $referencedItemPrice->itemPriceGroup->id,
                'custom_item_priceable_type' => ItemPriceGroup::class,
                'unit_id' => $referencedItemPrice->unit_id,
                'project_id' => $projectId,
                'name' => $referencedItemPrice->name,
                'price' => $request->price,
            ]);

        } else {

            $itemPrice = CustomItemPrice::where('project_id', $projectId)->where('code', $abstractItemPriceId)->first();

            if ($itemPrice) {

                $itemPrice->update($request->only([
                    'code', 'custom_item_priceable_id', 'custom_item_priceable_type', 'unit_id', 'name', 'price'
                ]));

                return response()->json([
                    'status' => 'success',
                    'data' => compact('itemPrice')
                ]);

            }

            return response()->json([
                'status' => 'fail',
                'message' => 'No custom itme price match with given criteria'
            ], 400);
        }
    }

    # Comparing custom item price with master item price
    private function compareItemPrice($masterItemPrices, $customItemPrices)
    {

        $masterItemPricesIdsMaps = $masterItemPrices->map(function($itemPrice) {

        });

        foreach ($customItemPrices as $customItemPrice) {

        }
    }
}
