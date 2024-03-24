<?php

namespace App\Http\Controllers;

use App\Models\Ahp;
use App\Models\Ahs;
use App\Models\CustomAhp;
use App\Models\CustomAhs;
use App\Models\CustomAhsItem;
use App\Models\CustomItemPrice;
use App\Models\CustomItemPriceGroup;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\Project;
use App\Models\Rab;
use Exception;
use Illuminate\Http\Request;
use App\Models\MasterRab;
use App\Models\RabItem;
use App\Models\RabItemHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

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
                    $q->with(['customAhs', 'implementationSchedule']);
                })->get();

        } else {
            $rabs = Rab::where('project_id', $project->hashidToId($project->hashid))
                ->with(['rabItemHeader.rabItem'])
                ->with('rabItem', function($q) {
                    $q->where('rab_item_header_id', NULL);
                    $q->with(['customAhs', 'implementationSchedule']);
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
        try {
            DB::beginTransaction();
            $request->merge(['project_id' => $project->hashidToId($project->hashid)]);

            if ($request->has('selectedMasterRabId') && $request->selectedMasterRabId != '-' && $request->selectedMasterRabId != '') {
                $masterRab = $this->showMasterRab($request->selectedMasterRabId, $project->province_id);
                if (count($masterRab) > 0) {
                    $rab = $this->createCustomRab($request, $project, $masterRab[0]);
                } else {
                    throw new Exception('Tidak dapat menemukan referensi RAB yang dipilih!');
                }
            } else {
                $rab = Rab::create($request->only(['name', 'project_id']));
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => compact('rab')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
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

    private function showMasterRab($masterRabId, $provinceId)
    {
        $rabs = MasterRab::with(['masterRabItemHeader.masterRabItem'])
            ->where('id', Hashids::decode($masterRabId)[0])
            ->with('masterRabItem', function($q) {
                $q->where('master_rab_item_header_id', NULL);
                $q->with(['ahs']);
            })
        ->get();

        $rabSubtotal = 0;

        foreach ($rabs as $key => $rab) {
            if ($rab->masterRabItem || ($rab->masterRabItemHeader && $rab->masterRabItemHeader->rabItem)) {
                foreach ($rab->masterRabItem as $key2 => $masterRabItem) {
                    if ($masterRabItem->ahs) {
                        $countedAhs = $this->countAhsSubtotal($masterRabItem->ahs, $provinceId);
                        $countedAhs->price = $countedAhs->subtotal;
                        $countedAhs->subtotal = $countedAhs->subtotal * ($masterRabItem->volume ?? 0);
                        $rabs[$key]->masterRabItem[$key2]['custom_ahs'] = $countedAhs;
                        $rabSubtotal += $countedAhs->subtotal;
                    } else {
                        $masterRabItem->subtotal = $masterRabItem->price * ($masterRabItem->volume ?? 0);
                        $rabs[$key]->masterRabItem[$key2] = $masterRabItem;
                        $rabSubtotal += $masterRabItem->subtotal;
                    }
                }

                foreach ($rab->masterRabItemHeader as $key3 => $masterRabItemHeader) {
                    foreach ($masterRabItemHeader->masterRabItem as $key4 => $masterRabItem) {
                        if ($masterRabItem->ahs) {
                            $countedAhs = $this->countAhsSubtotal($masterRabItem->ahs, $provinceId);
                            $countedAhs->price = $countedAhs->subtotal;
                            $countedAhs->subtotal = $countedAhs->subtotal * ($masterRabItem->volume ?? 0);
                            $masterRabItem['custom_ahs'] = $countedAhs;
                            $rabSubtotal += $countedAhs->subtotal;
                        } else {
                            $masterRabItem->subtotal = $masterRabItem->price * ($masterRabItem->volume ?? 0);
                            $rabSubtotal += $masterRabItem->subtotal;
                        }
                    }
                }
            } else {
                $rabSubtotal += 0;
            }

            $rabs[$key]->subtotal = $rabSubtotal;
            $rabSubtotal = 0;
        }

        return $rabs;
    }

    private function createCustomRab(Request $request, Project $project, $masterRab)
    {
        $customRab = Rab::create([
            'name' => $request->name,
            'project_id' => $project->id,
        ]);

        // Begin copying MasterRabItem to RabItem
        foreach ($masterRab->masterRabItem as $masterRabItem) {
            $customAhsId = null;
            if ($masterRabItem->ahs) {
                // Loop through AHS content, create the ahs item
                // Check if the user already have the AHS with the same identifier
                $userCustomAhs = CustomAhs::where('code', $masterRabItem->ahs->id)->where('project_id', $project->id)->first();
                if ($userCustomAhs) {
                    $customAhsId = $userCustomAhs->id;
                } else {
                    // Begin Custom AHS Creation
                    $customAhsId = $this->createCustomAhs($request, $project, $masterRabItem->ahs)->id;
                }
            }
            $rabItem = RabItem::create([
                'rab_id' => $customRab->id,
                'rab_item_header_id' => null,
                'name' => $masterRabItem->name,
                'custom_ahs_id' => $customAhsId,
                'volume' => $masterRabItem->volume,
                'price' => $masterRabItem->price,
                'unit_id' => $masterRabItem->unit_id,
            ]);
        }

        foreach ($masterRab->masterRabItemHeader as $masterRabItemHeader) {
            $customRabItemHeader = RabItemHeader::create([
                'rab_id' => $customRab->id,
                'name' => $masterRabItemHeader->name
            ]);

            foreach ($masterRabItemHeader->masterRabItem as $masterRabItem) {
                $customAhsId = null;
                if ($masterRabItem->ahs) {
                    // Loop through AHS content, create the ahs item
                    // Check if the user already have the AHS with the same identifier
                    $userCustomAhs = CustomAhs::where('code', $masterRabItem->ahs->id)->where('project_id', $project->id)->first();
                    if ($userCustomAhs) {
                        $customAhsId = $userCustomAhs->id;
                    } else {
                        // Begin Custom AHS Creation
                        $customAhsId = $this->createCustomAhs($request, $project, $masterRabItem->ahs)->id;
                    }
                }
                $rabItem = RabItem::create([
                    'rab_id' => $customRab->id,
                    'rab_item_header_id' => $customRabItemHeader->id,
                    'name' => $masterRabItem->name,
                    'custom_ahs_id' => $customAhsId,
                    'volume' => $masterRabItem->volume,
                    'price' => $masterRabItem->price,
                    'unit_id' => $masterRabItem->unit_id,
                ]);
            }
        }

        foreach ($masterRab->masterRabItem as $masterRabItem) {
            $customAhsId = null;
            if ($masterRabItem->ahs) {
                // Loop through AHS content, create the ahs item
                // Check if the user already have the AHS with the same identifier
                $userCustomAhs = CustomAhs::where('code', $masterRabItem->ahs->id)->where('project_id', $project->id)->first();
                if ($userCustomAhs) {
                    $customAhsId = $userCustomAhs->id;
                } else {
                    // Begin Custom AHS Creation
                    $customAhsId = $this->createCustomAhs($request, $project, $masterRabItem->ahs)->id;
                }
            }
            $rabItem = RabItem::create([
                'rab_id' => $customRab->id,
                'rab_item_header_id' => null,
                'name' => $masterRabItem->name,
                'custom_ahs_id' => $customAhsId,
                'volume' => $masterRabItem->volume,
                'price' => $masterRabItem->price,
                'unit_id' => $masterRabItem->unit_id,
            ]);
        }

        return $customRab;
    }

    private function createCustomAhs(Request $request, Project $project, $masterAhs)
    {

        $customAhs = CustomAhs::create([
            'code' => $masterAhs->id,
            'name' => $masterAhs->name,
            'project_id' => $project->id,
        ]);

        foreach ($masterAhs->ahsItem as $ahsItem) {

            $customAhsItemableType = null;
            $customAhsItemableId = null;

            switch ($ahsItem->ahs_itemable_type) {
                case ItemPrice::class:
                    // Check if user already have custom item price for item
                    $customItemPrice = CustomItemPrice::where('id', $ahsItem->ahs_itemable_id)->where('project_id', $project->id)->first();
                    $customAhsItemableType = CustomItemPrice::class;
                    if ($customItemPrice) {
                        $customAhsItemableId = $customItemPrice->id;
                    } else {
                        // Get item price group
                        $masterItemPriceGroup = ItemPriceGroup::find($ahsItem->ahsItemable->item_price_group_id);
                        $customItemPriceGroup = CustomItemPriceGroup::where('name', $masterItemPriceGroup->name)->where('project_id', $project->id)->first();
                        if (!$customItemPriceGroup) {
                            $customItemPriceGroup = CustomItemPriceGroup::create([
                                'project_id' => $project->id,
                                'name' => $masterItemPriceGroup->name,
                                'is_default' => true,
                            ]);
                        }
                        $customItemPrice = CustomItemPrice::create([
                            'code' => $ahsItem->ahsItemable->id,
                            'custom_item_price_group_id' => $customItemPriceGroup->id, # <- FIXME: Change to dynamic
                            'unit_id' => $ahsItem->ahsItemable->unit_id,
                            'project_id' => $project->id,
                            'name' => $ahsItem->ahsItemable->name,
                            'is_default' => false,
                            'price' => $ahsItem->ahsItemable->subtotal,
                            'default_price' => $ahsItem->ahsItemable->subtotal,
                        ]);
                        $customAhsItemableId = $customItemPrice->id;
                    }
                break;

                case Ahp::class:
                    $customAhp = CustomAhp::create([
                        'name' => $ahsItem->ahsItemable->name,
                        'code' => $ahsItem->ahsItemable->id,
                        'project_id' => $project->id,
                        'Pw' => $ahsItem->ahsItemable->Pw,
                        'Cp' => $ahsItem->ahsItemable->Cp,
                        'A' => $ahsItem->ahsItemable->A,
                        'W' => $ahsItem->ahsItemable->W,
                        'B' => $ahsItem->ahsItemable->B,
                        'i' => $ahsItem->ahsItemable->i,
                        'U1' => $ahsItem->ahsItemable->U1,
                        'U2' => $ahsItem->ahsItemable->U2,
                        'Mb' => $ahsItem->ahsItemable->Mb,
                        'Ms' => $ahsItem->ahsItemable->Ms,
                        'Mp' => $ahsItem->ahsItemable->Mp,
                        'pbb' => $ahsItem->ahsItemable->pbb,
                        'ppl' => $ahsItem->ahsItemable->ppl,
                        'pbk' => $ahsItem->ahsItemable->pbk,
                        'ppp' => $ahsItem->ahsItemable->ppp,
                        'm' => $ahsItem->ahsItemable->m,
                        'n' => $ahsItem->ahsItemable->n,
                        'is_default' => false,
                    ]);
                    $customAhsItemableId = $customAhp->id;
                    $customAhsItemableType = CustomAhp::class;
                break;

                case Ahs::class:
                    $customAhsItemableType = CustomAhs::class;
                    $customAhsItemableId = $this->createCustomAhs($request, $project, $ahsItem->ahsItemable)->id;
                break;
            }

            // if ($masterAhs->coefficient == null) {
            //     Log::info($ahsItem);
            // }

            CustomAhsItem::create([
                'custom_ahs_id' => $customAhs->id,
                'name' => $ahsItem->name,
                'unit_id' => $ahsItem->unit_id,
                'coefficient' => $ahsItem->coefficient ?? 0,
                'section' => $ahsItem->section,
                'custom_ahs_itemable_id' => $customAhsItemableId,
                'custom_ahs_itemable_type' => $customAhsItemableType,
            ]);
        }

        return $customAhs;
    }
}
