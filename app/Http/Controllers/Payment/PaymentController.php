<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\Snap;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
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

        // Check if the order already created or not
        $this->setOrder($orderId, $customer, Hashids::decode($request->project_id)[0]);

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

        $enabledPayments = ["credit_card", "cimb_clicks",
        "bca_klikbca", "bca_klikpay", "bri_epay", "echannel", "permata_va",
        "bca_va", "bni_va", "bri_va", "other_va", "gopay", "indomaret",
        "danamon_online", "akulaku", "shopeepay"];

        $transaction = [
            'enabled_payments' => $enabledPayments,
            'transaction_details' => $transactionDetails,
            'customer_detail' => $customerDetails,
            'item_details' => $buyedItems,
        ];

        try {
            $snapToken = Snap::getSnapToken($transaction);
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

    private function setOrder($orderId, $customer, $projectId)
    {
        Order::create([
            'order_id' => $orderId,
            'user_id' => $customer->id,
            'project_id' => $projectId,
        ]);
    }

    private function generateOrderId()
    {
        return strtoupper(Str::random(16));
    }
}
