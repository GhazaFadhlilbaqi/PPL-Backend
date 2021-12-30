<?php

namespace App\Http\Controllers;

use App\Models\CustomItemPriceGroup;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomItemPriceGroupController extends CustomItemPriceBaseController
{

    const ALLOWED_SEARCH_CRITERIA = ['header', 'item'];

    public function index(Project $project)
    {
        $customItemPriceGroups = $project->customItemPriceGroup()->with('customItemPrice')->get();

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPriceGroups')
        ]);
    }

    public function query(Project $project, Request $request)
    {

        $projectId = $project->hashidToId($project->hashid);
        $searchQuery = urldecode($request->q);
        $result = null;

        // Validate request query
        if (!$request->has('category') || $request->category == '' || !in_array($request->category, self::ALLOWED_SEARCH_CRITERIA)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Search category must be provided and between of [' . implode(', ', self::ALLOWED_SEARCH_CRITERIA) . ']'
            ], 422);
        }

        switch (strtolower($request->category)) {
            case self::ALLOWED_SEARCH_CRITERIA[0]:
                $result = $this->queryByHeader($projectId, $searchQuery);
            break;
            case self::ALLOWED_SEARCH_CRITERIA[1]:
                $result = $this->queryByItem($projectId, $searchQuery);
            break;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'result' => $result,
                'search_query' => $searchQuery,
                'search_by' => $request->category
            ]
        ]);
    }

    public function store(Project $project, Request $request)
    {

        $request->merge(['project_id' => $project->hashidToId($project->hashid)]);

        $customItemPrice = CustomItemPriceGroup::create($request->only([
            'name', 'project_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPrice')
        ]);
    }

    public function destroy(Project $project, CustomItemPriceGroup $customItemPriceGroup)
    {
        // TODO: Find all ahs that related to this item price

        $deps = [];

        foreach ($customItemPriceGroup->customItemPrice as $customItemPrice) {
            $currentDeps = $this->getCustomItemPriceDependencies($project->hashidToId($project->hashid), $customItemPrice->id);
            if ($currentDeps['ahs']->count() > 0) {
                $deps[$customItemPrice->id] = [
                    'customItemPrice' => $customItemPrice,
                    'deps' => $currentDeps,
                ];
            }
        }

        if (count($deps) > 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Data dari harga satuan di kelompok ini masih terhubung dengan data lain !'
            ], 400);
        }

        $customItemPriceGroup->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Custom item price group deleted'
        ], 200);
    }

    public function update(Project $project, CustomItemPriceGroup $customItemPriceGroup, Request $request)
    {
        $customItemPriceGroup->update($request->only([
            'name'
        ]));

        return response()->json([
            'status' => 'success',
        ]);
    }

    private function queryBySpecificResult($projectId, Request $request)
    {
        // Standart Se
        $itemPriceGroupsSearch = CustomItemPriceGroup::where('project_id', $projectId)->where(function($q) use ($projectId, $request) {
            $q->where('project_id', $projectId)->where('name', 'LIKE', '%' . $request->q . '%' );
        })->orWhere(function($q) use ($request) {
            $q->whereHas('customItemPrice', function($customItemPrice) use ($request) {
                $customItemPrice->where('name', 'LIKE', '%' . $request->q . '%');
            });
        })
        ->with(['customItemPrice' => function($r) use ($request) {
            $r->where('name', 'LIKE', '%' . $request->q . '%');
        }])
        ->get();

        return $itemPriceGroupsSearch;
    }

    private function queryByHeader($projectId, $searchQuery)
    {
        // Search by header
        $itemPriceGroupsSearch = CustomItemPriceGroup::where('project_id', $projectId)->where('name', 'LIKE', '%' . $searchQuery . '%')->with('customItemPrice')->get();

        return $itemPriceGroupsSearch;
    }

    private function queryByItem($projectId, $searchQuery)
    {
        // Standart Se
        $itemPriceGroupsSearch = CustomItemPriceGroup::where('project_id', $projectId)->whereHas('customItemPrice', function($q) use ($searchQuery) {
            $q->where('name', 'LIKE', '%' . $searchQuery . '%');
        })
        ->with('customItemPrice', function($q) use ($searchQuery) {
            $q->where('name', 'LIKE', '%' . $searchQuery . '%');
        })
        ->get();

        return $itemPriceGroupsSearch;
    }
}
