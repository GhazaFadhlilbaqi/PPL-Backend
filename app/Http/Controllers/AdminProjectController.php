<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class AdminProjectController extends Controller
{
    public function index(Request $request)
    {
        $userId = Hashids::decode($request->user_hash_id);
        if (!isset($userId[0])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User tidak ditemukan!',
            ], 400);
        }
        $query = Project::where('user_id', $userId)
            ->select('id', 'name', 'subscription_id', 'last_opened_at', 'created_at')
            ->with(['order' => function ($query) {
                $query->select('project_id', 'expired_at', 'is_active');
            }])
            ->with(['subscription' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('created_at', 'desc');
        $projects = $query->paginate($request->query('limit', 15));
        return response()->json([
            'status' => 'success',
            'data' => [
                'projects' => $projects->items(),
                'pagination_attribute' => [
                    'total_page' => $projects->lastPage(),
                    'total_data' => $projects->total()
                ]
            ]
        ]);
    }

    public function update(Project $project, Request $request)
    {
        $order = Order::where('project_id', $project->hashidToId($project->hashid))
            ->orderBy('created_at', 'desc')
            ->first();
        $order->update([
            'is_active' => true,
            'subscription_id' => $request['subscription_id'],
            'expired_at' => $request['expired_at']
        ]);
        $project->update($request->only([
            'name',
            'subscription_id'
        ]));
        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }
}
