<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\Snap;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class PaymentController extends Controller
{

    const productPrice = 10000;

    public function fetchSnapToken(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');

        if (!isset(Config::$serverKey)) return response()->json([
            'status' => 'fail',
            'message' => 'Please provide your midtrans server key !'
        ], 500);

        Config::$isSanitized = true;
        Config::$is3ds = true;

        // NOTE: For demo purpose only
        $buyedItems = [];
        $cartsSubtotal = self::productPrice;

        $customer = Auth::user();
        $orderId = $this->generateOrderId();
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
            if ($midtransStatusCode == '407' || $midtransStatusCode == '200') {
                $order->status = $midtransStatusCode == '407' ? 'expired' : 'completed';
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
            'Authorization' => 'Basic ' . base64_encode(env('MIDTRANS_SERVER_KEY') . ':'),
        ])->get($midtransApiUrl . '/v2' . '/' . $orderId . '/status');

        return $statusRequest->json();
    }
}
