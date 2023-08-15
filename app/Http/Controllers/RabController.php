<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rab;
use Illuminate\Http\Request;

class RabController extends CountableItemController
{

    const ALLOWED_SEARCH_CRITERIA = ['header', 'item'];

    public function index(Request $request, Project $project)
    {

        if ($request->has('q') && $request->q != '' && $request->has('category') && $request->category != '' && in_array($request->category, self::ALLOWED_SEARCH_CRITERIA)) {
            $rabs = Rab::where('project_id', $project->hashidToId($project->hashid));

            if ($request->category == 'header') {
                $rabs = $rabs->where('name', 'LIKE', '%' . $request->q . '%');
            } else {
                // TODO: Implement search by header feature
            }

            $rabs = $rabs->with(['rabItemHeader.rabItem'])
                ->with('rabItem', function($q) {
                    $q->where('rab_item_header_id', NULL);
                    $q->with('customAhs');
                })->get();

        } else {
            $rabs = Rab::where('project_id', $project->hashidToId($project->hashid))
                ->with(['rabItemHeader.rabItem'])
                ->with('rabItem', function($q) {
                    $q->where('rab_item_header_id', NULL);
                    $q->with('customAhs');
                })
                ->get();
        }

        $rabSubtotal = 0;

        foreach ($rabs as $key => $rab) {
            if ($rab->rabItem || ($rab->rabItemHeader && $rab->rabItemHeader->rabItem)) {
                foreach ($rab->rabItem as $key2 => $rabItem) {
                    if ($rabItem->customAhs) {
                        $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
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

                // foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {
                //     foreach ($rabItemHeader->rabItem as $rabItem) {
                //         if ($rabItem->customAhs) {
                //             $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                //             $countedAhs->price = $countedAhs->subtotal;
                //             $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                //             $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                //             $rabSubtotal += $countedAhs->subtotal;
                //         } else {
                //             $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                //             $rabSubtotal += $rabItem->subtotal;
                //         }
                //     }
                // }

                foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {
                    foreach ($rabItemHeader->rabItem as $rabItem) {
                        if ($rabItem->customAhs) {
                            $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
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

    public function store(Request $request, Project $project)
    {

        $request->merge(['project_id' => $project->hashidToId($project->hashid)]);
        $rab = Rab::create($request->only(['name', 'project_id']));

        return response()->json([
            'status' => 'success',
            'data' => compact('rab')
        ]);
    }

    public function update(Request $request, Project $project, Rab $rab)
    {
        $rab->update($request->only(['name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('rab'),
        ]);
    }

    public function destroy(Project $project, Rab $rab)
    {

        $rab->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }
}
