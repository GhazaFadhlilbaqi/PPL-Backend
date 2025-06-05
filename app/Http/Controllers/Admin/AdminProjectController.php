<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\OrderHelper;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectTemporary;
use App\Models\SubscriptionPrice;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Vinkla\Hashids\Facades\Hashids;

class AdminProjectController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

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

    public function store(Project $project, Request $request)
    {
        $validatedRequest = $request->validate([
            'user_hash_id'          => 'required|string|max:255',
            'name'                  => 'required|string|max:255',
            'activity'              => 'nullable|string',
            'job'                   => 'nullable|string',
            'address'               => 'nullable|string',
            'province_id'           => 'required|exists:provinces,id',
            'fiscal_year'           => 'required|integer',
            'profit_margin'         => 'nullable|numeric|min:0|max:100',
            'ppn'                   => 'nullable|numeric|min:0|max:100',
            'subscription_price_id' => 'required|exists:subscription_prices,id',
        ]);
        $validatedRequest['user_id'] = Hashids::decode($request->user_hash_id)[0];

        DB::beginTransaction();

        try {
            $subscriptionPrice = SubscriptionPrice::find($validatedRequest['subscription_price_id']);
            $order = $this->orderService->createOrder([
                'user_id' => $validatedRequest['user_id'],
                'type' => 'create'
            ], $subscriptionPrice);

            ProjectTemporary::create([
                'user_id' => $validatedRequest['user_id'],
                'order_id' => $order->id,
                'name' => $validatedRequest['name'],
                'activity' => $validatedRequest['activity'],
                'job' => $validatedRequest['job'],
                'address' => $validatedRequest['address'],
                'province_id' => $validatedRequest['province_id'],
                'fiscal_year' => $validatedRequest['fiscal_year'],
                'profit_margin' => $validatedRequest['profit_margin'],
                'ppn' => $validatedRequest['ppn']
            ]);

            OrderHelper::setOrderAsSuccessful($order);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil dibuat',
                'data' => null
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ], 422);
            }

            return response()->json([
                'status' => 'fail',
                'message' => 'Order gagal dibuat',
                'trace' => $e->getTrace()
            ], 409);
        }
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
