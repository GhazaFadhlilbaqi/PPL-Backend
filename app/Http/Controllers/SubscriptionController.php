<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['features', 'prices'])
            ->orderBy('order', 'ASC')
            ->where('id', '!=', 'demo')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                "subscriptions" => SubscriptionResource::collection($subscriptions)
            ]
        ]);
    }
}
