<?php

namespace App\Http\Controllers;

use App\Models\Ahp;
use App\Models\Ahs;
use App\Models\CustomAhp;
use App\Models\CustomAhs;
use App\Models\CustomAhsItem;
use App\Models\CustomItemPrice;
use App\Models\ItemPrice;
use App\Models\Project;
use App\Models\Rab;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class CustomAhsController extends CountableItemController
{

    const ALLOWED_SEARCH_CRITERIA = ['header', 'item'];

    public function index(Project $project, Request $request)
    {

        $isPaginatedRequest = $request->has('page') && $request->page > 0;
        $paginationAttribute = [];

        $customAhs = CustomAhs::where('project_id', $project->hashidToId($project->hashid))->with(['customAhsItem' => function($q) {
            $q->with(['unit', 'customAhsItemable']);
        }]);

        # Paginate Custom AHS
        if ($isPaginatedRequest) {
            $paginationResult = $this->paginateCustomAhs($customAhs, $request->page, $request->per_page);
            $customAhs = $paginationResult['customAhs'];
            $paginationAttribute['total_page'] = $paginationResult['total_page'];
            $paginationAttribute['total_rows'] = $paginationResult['total_rows'];
        }

        $customAhs = $customAhs->get();

        # Arrange Custom AHS
        if ($request->has('arrange') && $request->arrange == 'true') {

            $arrangedCustomAhs = [];

            foreach ($customAhs as $key => $cAhs) {
                foreach ($cAhs->customAhsItem as $cAhsItem) $arrangedCustomAhs[$cAhsItem->section][] = $cAhsItem;
                $customAhs[$key]['item_arranged'] = $arrangedCustomAhs;
                $arrangedCustomAhs = [];
                $customAhs[$key] = $this->countCustomAhsSubtotal($cAhs, $project->province->id);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'customAhs' => $customAhs,
                'pagination_attribute' => $paginationAttribute,
            ],
        ]);
    }

    // FIXME: Using validation request
    public function update(Project $project, CustomAhs $customAhs, Request $request)
    {

        // TODO: Implement update validation, update all child if code updated !
        $customAhs->update($request->only([
            'code', 'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhs')
        ]);
    }

    public function destroy(Project $project, CustomAhs $customAhs)
    {
        // Check it's dependency
        $deps = $this->getCustomAhsDependencies($project->hashidToId($project->hashid), $customAhs->id);
        $hasDependencies = $deps['rab']->count() > 0 || $deps['customAhs']->count() > 0;

        // FIXME: Give user information about what it's dependencies so user can easily resolve it !
        if ($hasDependencies) {
            return response()->json([
                'status' => 'fail',
                'message' => 'AHS ini masih terhubung dengan data RAB / AHS lain'
            ], 400);
        }

        $customAhs->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }

    // FIXME: Using validation request
    public function store(Project $project, Request $request)
    {

        $request->merge([
            'project_id' => $project->hashidToId($project->hashid)
        ]);

        // TODO: Implement validation for same project and ahs id !
        $sameCodeAhs = $project->customAhs->where('code', $request->code)->first();

        if ($sameCodeAhs) return response()->json([
            'errors' => [
                'code' => ['Kode AHS ini sudah digunakan !']
            ]
        ], 422);

        if ($request->has('selected_reference') && $request->selected_reference) {
            $this->copyCustomAhsFromAhs($project, $request->selected_reference, $request);
        } else {
            $customAhs = CustomAhs::create($request->only([
                'name', 'code', 'project_id'
            ]));
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function getAhsIds(Project $project)
    {
        $ahsItemIds = $project->customAhs->map(function($data) {
            return [
                'hashid' => $data->hashid,
                'code' => $data->code,
                'name' => $data->name
            ];
        })->toArray();

        return response()->json([
            'status' => 'success',
            'data' => compact('ahsItemIds')
        ]);
    }

    public function query(Project $project, Request $request)
    {

        if (!$request->has('category') || $request->category == '' || !in_array($request->category, self::ALLOWED_SEARCH_CRITERIA)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Search category must be provided and between of [' . implode(', ', self::ALLOWED_SEARCH_CRITERIA) . ']'
            ]);
        }

        $customAhs = CustomAhs::where('project_id', $project->hashidToId($project->hashid));
        $x = [];

        // TODO: Implement item search
        if ($request->category == 'header') {
            $customAhs = $customAhs->where('name', 'LIKE', '%' . $request->q . '%')->orWhere('code', 'LIKE', '%' . $request->q . '%')->with(['customAhsItem' => function($q) use ($request) {
                $q->with(['unit', 'customAhsItemable']);
            }])->get();
        } else {
            // $customAhs = $customAhs->whereHas('customAhsItem', function($q) use ($request, $x) {}) ;
        }

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $x,
        // ]);

        if ($request->has('arrange') && $request->arrange == 'true') {

            $arrangedCustomAhs = [];

            foreach ($customAhs as $key => $cAhs) {
                foreach ($cAhs->customAhsItem as $cAhsItem) $arrangedCustomAhs[$cAhsItem->section][] = $cAhsItem;
                $customAhs[$key]['item_arranged'] = $arrangedCustomAhs;
                $arrangedCustomAhs = [];
                $customAhs[$key] = $this->countCustomAhsSubtotal($cAhs, $project->province->id);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $customAhs,
        ]);
    }

    private function copyCustomAhsFromAhs(Project $project, $ahsReferenceId, Request $request)
    {

        $referencedAhs = Ahs::find($ahsReferenceId);

        if ($referencedAhs) {

            DB::transaction(function() use ($project, $referencedAhs, $request) {

                $customAhsItemRemapped = [];

                # Check if it's referenced from other AHS or not, when it is, then refuse it !
                foreach ($referencedAhs->ahsItem as $ahsItem) {
                    if ($ahsItem->ahs_itemable_type == Ahs::class) {
                        return false;
                    }
                }

                $customAhs = CustomAhs::create([
                    'code' => $request->code,
                    'name' => $request->name,
                    'project_id' => $project->hashidToId($project->hashid),
                ]);

                foreach ($referencedAhs->ahsItem as $ahsItem2) {

                    $relatedDependency = $this->getRelatedCustomAhsItemDependency($ahsItem2, $project->hashidToId($project->hashid));

                    $customAhsItemRemapped[] = [
                        'custom_ahs_id' => $customAhs->id,
                        'name' => $ahsItem2->name,
                        'unit_id' => $ahsItem2->unit_id,
                        'coefficient' => $ahsItem2->coefficient,
                        'section' => $ahsItem2->section,
                        'custom_ahs_itemable_id' => $relatedDependency['model']->id,
                        'custom_ahs_itemable_type' => $relatedDependency['type'],
                        'created_at' => Carbon::now()
                    ];
                }

                CustomAhsItem::insert($customAhsItemRemapped);

            });

        } else {
            throw new Exception('No parent reference found');
        }
    }

    private function getRelatedCustomAhsItemDependency($ahsItem, $projectId)
    {
        switch ($ahsItem->ahs_itemable_type) {
            case Ahp::class :
                return [
                    'model' => CustomAhp::where('code', $ahsItem->ahsItemable->id)->where('project_id', $projectId)->first(),
                    'type' => CustomAhp::class,
                ];
            case ItemPrice::class :
                return [
                    'model' => CustomItemPrice::where('code', $ahsItem->ahsItemable->id)->where('project_id', $projectId)->first(),
                    'type' => CustomItemPrice::class,
                ];
            default :
                throw new Exception('No compatible itemable class');
        }
    }

    private function getCustomAhsDependencies($projectId, $customAhsId)
    {
        $rabDeps = Rab::where('project_id', $projectId)->whereHas('rabItem', function($q) use ($customAhsId) {
            $q->where('custom_ahs_id', $customAhsId);
        })->get();

        $customAhsDeps = CustomAhs::where('project_id', $projectId)->whereHas('customAhsItem', function($q) use ($customAhsId) {
            $q->where('custom_ahs_itemable_type', CustomAhs::class)->where('custom_ahs_itemable_id', $customAhsId);
        })->get();

        return [
            'rab' => $rabDeps,
            'customAhs' => $customAhsDeps
        ];
    }

    private function paginateCustomAhs($customAhs, $currentPage, $ahsPerPage)
    {
        $totalRows = $customAhs->count();
        $totalPage = ceil($totalRows / (int) $ahsPerPage);
        $currentIndexStart = ((int) $ahsPerPage * (int) $currentPage) - (int) $ahsPerPage;

        $customAhs = $customAhs->skip($currentIndexStart)->take((int) $ahsPerPage);

        return [
            'total_page' => $totalPage,
            'current_page' => $currentPage,
            'current_index_range' => [$currentIndexStart, $currentIndexStart + (int) $ahsPerPage],
            'total_rows' => $totalRows,
            'customAhs' => $customAhs
        ];
    }
}
