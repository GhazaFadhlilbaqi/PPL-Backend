<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterRab;
use App\Models\Rab;
use Illuminate\Http\Request;
use Illuminate\Support\Js;

class RabController extends Controller
{

    const ALLOWED_SEARCH_CRITERIA = ['header', 'item'];

    public function index(Request $request)
    {
        if ($request->has('q') && $request->q != '' && $request->has('category') && $request->category != '' && in_array($request->category, self::ALLOWED_SEARCH_CRITERIA)) {
            $rabs = MasterRab::query();

            if ($request->category == 'header') {
                $rabs = $rabs->where('name', 'LIKE', '%' . $request->q . '%');
            } else {
                // TODO: Implement search by header feature
            }

            $rabs = $rabs->with(['masterRabItemHeader.masterRabItem'])
                ->with('masterRabItem', function($q) {
                    $q->where('master_rab_item_header_id', NULL);
                    $q->with(['ahs']);
                })->get();

        } else {
            $rabs = MasterRab::with(['masterRabItemHeader.masterRabItem'])
                ->with('masterRabItem', function($q) {
                    $q->where('master_rab_item_header_id', NULL);
                    $q->with(['ahs']);
                })
                ->get();
        }

        $rabSubtotal = 0;

        foreach ($rabs as $key => $rab) {
            if ($rab->rabItem || ($rab->rabItemHeader && $rab->rabItemHeader->rabItem)) {
                foreach ($rab->rabItem as $key2 => $rabItem) {
                    if ($rabItem->ahs) {
                        $countedAhs = $this->countAhsSubtotal($rabItem->ahs, 1);
                        $countedAhs->price = $countedAhs->subtotal;
                        $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                        $rabSubtotal += $countedAhs->subtotal;
                    } else {
                        $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2] = $rabItem;
                        $rabSubtotal += $rabItem->subtotal;
                    }
                }

                foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {
                    foreach ($rabItemHeader->rabItem as $rabItem) {
                        if ($rabItem->ahs) {
                            $countedAhs = $this->countCustomAhsSubtotal($rabItem->ahs);
                            $countedAhs->price = $countedAhs->subtotal;
                            $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                            $rabs[$key]->rabItemHeader[$key3]['custom_ahs'] = $countedAhs;
                            $rabSubtotal += $countedAhs->subtotal;
                        } else {
                            $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                            $rabSubtotal += $rabItem->subtotal;
                        }
                    }
                }
            } else {
                $rabSubtotal += 0;
            }

            $rabs[$key]->subtotal = $rabSubtotal;
            $rabSubtotal = 0;
        }

        return response()->json([
            'status' => 'success',
            'data' => compact('rabs')
        ]);
    }

    public function store(Request $request)
    {
        $rab = MasterRab::create($request->only(['name']));
        return response()->json([
            'status' => 'success',
            'data' => compact('rab')
        ]);
    }
}
