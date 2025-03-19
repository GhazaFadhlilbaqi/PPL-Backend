<?php

namespace App\Http\Controllers;

use App\Models\Ahs;
use App\Models\CustomAhs;
use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Unit;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class RabItemController extends Controller
{

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
          'rab_id', 'name', 'custom_ahs_id', 'volume', 'unit_id', 'rab_item_header_id'
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
          'name', 'custom_ahs_id', 'volume', 'unit_id', 'price', 'profit_margin'
      ]));

      return response()->json([
          'status' => 'success',
          'data' => compact('rabItem')
      ]);
  }

  public function updateAhs(Project $project, Rab $rab, RabItem $rabItem, Request $request) {
    // 1) Check duplicated AHS project
    $savedCustomAhs = $project->customAhs
        ->where('code', $request->ahs_id)
        ->first();
    if ($request->group_id && $savedCustomAhs) {
      return response()->json([
        'message' => 'Kode AHS ini sudah digunakan !'
      ], 422);
    };

    // 2) Create new AHS project when requested ahs is not AHS project
    if ($request->group_id) {
      $request = $this->createNewAHSProject($project, $request);
    }
    $rabItem->update([
      'custom_ahs_id' => Hashids::decode($request->ahs_id)[0]
    ]);

    // 3) Calculate custom AHS price
    $customAhs = CustomAhs::where(['id' => Hashids::decode($request->ahs_id)[0]])
      ->with('customAhsItem.customAhsItemable')
      ->first();
    $customAhs->price = 0;
    foreach ($customAhs->customAhsItem as $customAhsItem) {
      $vendorPrice = ($customAhsItem->customAhsItemable->price * $customAhsItem->coefficient);
      $projectPrice = $vendorPrice + ($vendorPrice * ($project->profit_margin/100));
      $customAhs->price = $customAhs->price + $projectPrice;
    }

    // 4) Send response to client
    return response()->json([
      'status' => 'success',
      'data' => compact('customAhs')
    ]); 
  }

  public function destroy(Project $project, Rab $rab, RabItem $rabItem)
  {
      $rabItem->delete();

      return response()->json([
          'status' => 'success'
      ], 204);
  }

  // MARK: Private Functions

  private function createNewAHSProject(Project $project, Request $request) {
    $masterAhs = Ahs::where(['id' => $request->ahs_id])->first();
    $request->merge([
      'code' => $masterAhs->id,
      'name' => $masterAhs->name,
      'project_id' => $project->hashidToId($project->hashid)
    ]);
    (new CustomAhsController)->copyCustomAhsFromAhs($project, $request->ahs_id, $request);
    $customAhs = CustomAhs::where([
      'code' => $request->code,
      'project_id' => $project->hashidToId($project->hashid)
    ])->first();
    $request['ahs_id'] = $customAhs->hashid;
    return $request;
  }
}
