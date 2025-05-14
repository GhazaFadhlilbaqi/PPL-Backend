<?php

namespace App\Http\Controllers\Payment;

use App\Enums\SubscriptionDurationType;
use App\Helpers\OrderHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\Snap;
use App\Models\Order;
use App\Models\ProjectTemporary;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;

class PaymentController extends Controller
{

    const productPrice = 10000;

    public function fetchSnapToken(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_MODE') == 'sandbox' ? env('MIDTRANS_SERVER_KEY_DEVELOPMENT') : env('MIDTRANS_SERVER_KEY_PRODUCTION');
        if (!isset(Config::$serverKey)) return response()->json([
            'status' => 'fail',
            'message' => 'Please provide your midtrans server key !'
        ], 500);

        Config::$isSanitized = true;
        Config::$is3ds = true;

        // NOTE: For demo purpose only
        $buyedItems = [];
        $cartsSubtotal = self::productPrice;

        if ($request->has('project_id') && $request->project_id) {
            $customer = Auth::user();
            $orderId = generateRandomOrderId();
            $projectId = Hashids::decode($request->project_id)[0];

            /**
             * Payment verification step
             *
             * 1. Check if there's any pending or waiting_for_payment order
             * 2. Check for midtrans status
             * 3. Re use order if it's not expired (status from midtrans)
             */

            $order = Auth::user()->order()->where('project_id', $projectId)->orderBy('created_at', 'DESC')->take(1)->first();

            if ($order && in_array($order->status, ['pending', 'waiting_for_payment']) && $order->used_at == null) {

                // Check midtrans status
                $midtransStatus = $this->checkStatusOrder($order->order_id);
                $midtransStatusCode = $midtransStatus['status_code'];

                // If it's expired or it's completed (but marked as pending or expired)
                if ($midtransStatusCode == '407' || $midtransStatusCode == '200' || $midtransStatusCode == '404') {
                    $order->status = $midtransStatusCode == '407' ? 'expired' : ($midtransStatusCode == '404' ? 'canceled' : 'completed');
                    $order->save();
                    $order = $this->setOrder($orderId, $customer, Hashids::decode($request->project_id)[0], self::productPrice);
                }
            } else {
                $order = $this->setOrder($orderId, $customer, Hashids::decode($request->project_id)[0], self::productPrice);
            }

            // Check if there's a pending order
            // $pendingOrder = Auth::user()->order()->where('project_id', $projectId)->where('status', 'pending')->first();

            // if ($pendingOrder) {
            //     return response()->json([
            //         'status' => 'fail',
            //         'message' => 'Masih terdapat pembayaran yang pending ! mohon tunggu hingga anda mendapatkan email konfirmasi pembayaran'
            //     ], 400);
            // }

            // Check if the order already created or not
            // $order = Auth::user()->order()->where('project_id', $projectId)->where('status', 'waiting_for_payment')->first();

            $buyedItems[] = [
                'id' => uniqid(),
                'price' => self::productPrice,
                'quantity' => 1,
                'name' => 'Export RAB user ' . $customer->first_name . ' ' . ($customer->last_name ?? ''),
            ];

            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => $cartsSubtotal
            ];

            $customerDetails = [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone_number,
            ];

            // NOTE: Metode yg lain butuh handle yang lain jg
            // $enabledPayments = [
            //     "credit_card", "cimb_clicks", "bca_klikbca", "bca_klikpay", "bri_epay", "echannel", "permata_va", "bca_va", "bni_va", "bri_va", "other_va", "gopay", "indomaret", "danamon_online", "shopeepay"
            // ];

            $enabledPayments = [
                "credit_card"
            ];

            $transaction = [
                'enabled_payments' => $enabledPayments,
                'transaction_details' => $transactionDetails,
                'customer_detail' => $customerDetails,
                'item_details' => $buyedItems,
            ];

            try {
                $snapToken = $order->midtrans_snap_token ?? Snap::getSnapToken($transaction);

                $order->midtrans_snap_token = $snapToken;
                $order->save();

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'token' => $snapToken,
                    ]
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'fail',
                    'message' => $e->getMessage()
                ], 500);
            }
        } else if ($request->has('order_id') && $request->order_id) {

            $order = Order::where('order_id', $request->order_id)->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $order->midtrans_snap_token,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Please provide a project_id or order_id !',
            ], 400);
        }
    }

    public function fetchSubscriptionSnapToken(Request $request)
    {

        DB::beginTransaction();

        $request->validate([
            'subscription_type' => ['required', 'in:MONTHLY,YEARLY']
        ]);

        if ($request->type != 'create' && $request->type != 'renew') {
            throw new Exception('Invalid type');
        }

        $subscription = Subscription::find($request->subscription_id);
        $subscriptionPrice = $request->subscription_type === SubscriptionDurationType::YEARLY->value
            ? $subscription->yearly_price * 12
            : $subscription->monthly_price * $subscription->min_month;

        MidtransConfig::$serverKey = config('app.midtrans_env')
            ? env('MIDTRANS_SERVER_KEY_DEVELOPMENT')
            : env('MIDTRANS_SERVER_KEY_PRODUCTION');
        MidtransConfig::$isProduction = config('app.midtrans_env') == 'production';
        MidtransConfig::$is3ds = true;

        $user = Auth::user();

        // Generate order data
        $order = Order::create([
            'order_id' => $this->generateOrderId(),
            'user_id' => $user->id,
            'project_id' => $request->type == 'create' ? null : Hashids::decode($request->project_hashid)[0],
            'subscription_id' => $subscription->id,
            'subscription_duration_type' => $request->subscription_type,
            'status' => 'waiting_for_payment',
            'gross_amount' => $subscriptionPrice,
            'type' => $request->type == 'create' ? 'create' : 'renew'
        ]);

        // Check if the action is creating project or renewing project
        if ($request->type == 'create') {
            // This temporary project will become the "temporary" storage for project creation. It also
            // act as order identifier towards project data.
            ProjectTemporary::create([
                'user_id' => Auth::user()->id,
                'order_id' => $order->id,
                'name' => $request->name,
                'activity' => $request->activity,
                'job' => $request->job,
                'address' => $request->address,
                'province_id' => Hashids::decode($request->province_id)[0],
                'fiscal_year' => $request->fiscal_year,
                'profit_margin' => $request->margin_profit,
                'ppn' => $request->ppn,
            ]);
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_id,
                'gross_amount' => $order->gross_amount,
            ],
            'item_details' => [
                [
                    'id' => $subscription->hashid,
                    'price' => $subscriptionPrice,
                    'quantity' => 1,
                    'name' => 'Project Plan : ' . $subscription->name,
                ]
            ],
            'customer_details' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'address' => $user->address,
                'phone' => $user->phone,
                'email' => $user->email,
            ],
        ];

        $snapToken = null;

        try {
            $snapToken = MidtransSnap::getSnapToken($params);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        $order->midtrans_snap_token = $snapToken;
        $order->save();

        // Set order as successful when laravel env is local
        if (env('APP_ENV') === 'local') {
            OrderHelper::setOrderAsSuccessful($order);
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => [
                'snap_token' => $snapToken,
            ]
        ]);
    }

    public function setCanceled(Request $request)
    {
        $order = Order::where('midtrans_snap_token', $request->snapToken)->first();

        if ($order->status == 'waiting_for_payment') {
            $order->status = 'canceled';
            $order->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order canceled',
        ]);
    }

    public function setPending(Request $request)
    {

        $order = Order::where('midtrans_snap_token', $request->snapToken)->first();

        // Make sure the current order is still on waiting_for_payment status. Otherwise, it could be
        // reversed status (e.g from completed to pending)
        if ($order->status == 'waiting_for_payment') {
            $order->status = 'pending';
            $order->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Set pending successfully !',
        ]);
    }

    public function addToken(Request $request)
    {
        $user = Auth::user();
        $user->token_amount += $request->token_amount;
        $user->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_token_amount' => $user->token_amount,
            ]
        ]);
    }

    private function setOrder($orderId, $customer, $projectId, $grossAmount)
    {
        $order = Order::create([
            'order_id' => $orderId,
            'user_id' => $customer->id,
            'project_id' => $projectId,
            'gross_amount' => $grossAmount,
        ]);

        return $order;
    }

    private function generateOrderId()
    {
        return strtoupper(Str::random(16));
    }

    private function checkStatusOrder($orderId)
    {

        $midtransApiUrl = env('MIDTRANS_MODE') == 'production' ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        $statusRequest = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode((env('MIDTRANS_MODE') ? env('MIDTRANS_SERVER_KEY_DEVELOPMENT') : env('MIDTRANS_SERVER_KEY_PRODUCTION')) . ':'),
        ])->get($midtransApiUrl . '/v2' . '/' . $orderId . '/status');

        return $statusRequest->json();
    }
}
