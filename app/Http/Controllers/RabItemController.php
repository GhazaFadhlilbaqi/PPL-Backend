<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\CustomAhs;
use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Unit;
use App\Services\CustomAhsService;
use Illuminate\Http\Request;
use Throwable;
use Vinkla\Hashids\Facades\Hashids;

class RabItemController extends Controller
{

  protected $customAhsService;

  public function __construct(CustomAhsService $customAhsService)
  {
    $this->customAhsService = $customAhsService;
  }

  // MARK: Public Function

  public function index(Project $project, Rab $rab)
  {
    $rabItems = $rab->rabItem;

    return response()->json([
      'status' => 'success',
      'data' => compact('rabItems')
    ]);
  }

  public function store(Project $project, Rab $rab, Request $request)
  {

    $request->merge([
      'rab_id' => $rab->hashidToId($rab->hashid),
      'unit_id' => ($request->has('unit_id') && $request->rab_item_header_id) ? Unit::findByHashid($request->unit_id)->id : Unit::first()->id,
      'rab_item_header_id' => ($request->has('rab_item_header_id') && $request->rab_item_header_id) ? Hashids::decode($request->rab_item_header_id)[0] : NULL,
    ]);

    $rabItem = RabItem::create($request->only([
      'rab_id',
      'name',
      'custom_ahs_id',
      'volume',
      'unit_id',
      'rab_item_header_id'
    ]));

    return response()->json([
      'status' => 'success',
      'data' => compact('rabItem'),
    ]);
  }

  public function update(Project $project, Rab $rab, RabItem $rabItem, Request $request)
  {

    $dataToMerge = [];

    if ($request->has('unit_id') && $request->unit_id) $dataToMerge['unit_id'] = Hashids::decode($request->unit_id)[0];
    if ($request->has('custom_ahs_id') && $request->custom_ahs_id) $dataToMerge['custom_ahs_id'] = Hashids::decode($request->custom_ahs_id)[0];

    $request->merge($dataToMerge);

    $rabItem->update($request->only([
      'name',
      'custom_ahs_id',
      'volume',
      'unit_id',
      'price',
      'profit_margin'
    ]));

    return response()->json([
      'status' => 'success',
      'data' => compact('rabItem')
    ]);
  }

  public function destroy(Project $project, Rab $rab, RabItem $rabItem)
  {
    $rabItem->delete();

    return response()->json([
      'status' => 'success'
    ], 204);
  }

  public function updateAhs(Project $project, Rab $rab, RabItem $rabItem, Request $request)
  {
    try {
      // 1. Find or create custom ahs based on selected ahs
      if ($request->referenceGroupId) {
        $custom_ahs = $this->customAhsService->customFromMasterAhs(
          $project,
          $request->ahs_id,
          $request->referenceGroupId
        );
        if ($custom_ahs === null) {
          throw new CustomException("AHS tidak ditemukan!");
        }
      } else {
        $custom_ahs = CustomAhs::where(['id' => Hashids::decode($request->ahs_id)[0]])->first();
      }

      // 2. Update rab item with selected custom ahs
      $rabItem->update(['custom_ahs_id' => $custom_ahs->id]);

      // 3. Calculate custom ahs price based on project
      $custom_ahs->price = $this->customAhsService->calculateCustomAhsPrice(
        $project->profit_margin,
        $custom_ahs
      );

      return response()->json([
        'status' => 'success',
        'data' => ['customAhs' => $custom_ahs]
      ]);
    } catch (Throwable $e) {
      return response()->json([
        'status' => 'failed',
        'message' => $e->getMessage()
      ], 409);
    }
  }
}
