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
use App\Models\Project;
use App\Models\Rab;
use App\Services\CustomAhsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CustomAhsController extends CountableItemController
{

    const ALLOWED_SEARCH_CRITERIA = ['header', 'item'];

    public function index(Project $project, Request $request)
    {

        $isPaginatedRequest = $request->has('page') && $request->page > 0;
        $paginationAttribute = [];

        $customAhs = CustomAhs::where('project_id', $project->hashidToId($project->hashid))->with(['customAhsItem' => function ($q) {
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

    public function store(Project $project, Request $request, CustomAhsService $customAhsService)
    {
        try {
            $isAhsExists = $project->customAhs
                ->where('code', $request->code)
                ->isNotEmpty();

            if ($isAhsExists) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Kode AHS ini sudah digunakan !'
                ], 409);
            };

            if ($request->selected_reference) {
                $masterAhs = Ahs::where('code', $request->code)->first();
                $customAhsService->customFromMasterAhs(
                    $project,
                    $masterAhs->id,
                    $request->selected_reference
                );
            } else {
                CustomAhs::create([
                    'project_id' => $project->id,
                    'code' => $request->code,
                    'name' => $request->name
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => null
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 409);
        }
    }

    // FIXME: Using validation request
    public function update(Project $project, CustomAhs $customAhs, Request $request)
    {

        // TODO: Implement update validation, update all child if code updated !
        $customAhs->update($request->only([
            'code',
            'name'
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

    public function getAhsIds(Project $project, Request $request)
    {
        $customAhsQuery = CustomAhs::query();
        if ($request->has('limit')) {
            $customAhsQuery->take($request->limit);
        }
        $customAhsItems = $customAhsQuery->with('customAhsItem.customAhsItemable')
            ->where(['project_id' => $project->hashidToId($project->hashid)])
            ->where(function ($query) use ($request) {
                $query->where('code', 'LIKE', "%$request->q%")
                    ->orWhere('name', 'LIKE', "%$request->q%");
            })
            ->latest()
            ->get();
        $ahsItemIds = $customAhsItems->map(function ($data) use ($project) {
            $price = 0;
            foreach ($data->customAhsItem as $customAhsItem) {
                $price += $customAhsItem->customAhsItemable->price * $customAhsItem->coefficient;
            }
            return [
                'hashid' => $data->hashid,
                'code' => $data->code,
                'name' => $data->name,
                'price' => $price + (($project->profit_margin / 100) * $price)
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
            $customAhs = $customAhs->where('name', 'LIKE', '%' . $request->q . '%')->orWhere('code', 'LIKE', '%' . $request->q . '%')->with(['customAhsItem' => function ($q) use ($request) {
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

    private function getRelatedCustomAhsItemDependency($ahsItem, $projectId)
    {
        switch ($ahsItem->ahs_itemable_type) {
            case Ahp::class:
                return [
                    'model' => CustomAhp::where('code', $ahsItem->ahsItemable->id)->where('project_id', $projectId)->first(),
                    'type' => CustomAhp::class,
                ];
            case ItemPrice::class:
                return [
                    'model' => CustomItemPrice::where('code', $ahsItem->ahsItemable->id)->where('project_id', $projectId)->first(),
                    'type' => CustomItemPrice::class,
                ];
            default:
                throw new Exception('No compatible itemable class');
        }
    }

    private function getCustomAhsDependencies($projectId, $customAhsId)
    {
        $rabDeps = Rab::where('project_id', $projectId)->whereHas('rabItem', function ($q) use ($customAhsId) {
            $q->where('custom_ahs_id', $customAhsId);
        })->get();

        $customAhsDeps = CustomAhs::where('project_id', $projectId)->whereHas('customAhsItem', function ($q) use ($customAhsId) {
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
