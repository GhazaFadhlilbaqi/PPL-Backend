<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {

        $subscriptions = Subscription::orderBy('order', 'ASC')->get(['id', 'name', 'price', 'subscription_type', 'description', 'is_show']);

        return response()->json([
            'status' => 'success',
            'data' => compact('subscriptions')
        ]);
    }
}
