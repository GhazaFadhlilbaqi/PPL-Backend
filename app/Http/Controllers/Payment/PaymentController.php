<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\Snap;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
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
        $cartsSubtotal = 0;

        foreach ($request->carts as $product) {
            $buyedItems[] = [
                'id' => $product['id'],
                'price' => $product['priceRaw'],
                'quantity' => 1 ,
                'name' => $product['title'],
            ];

            $cartsSubtotal += $product['priceRaw'];
        }

        $transactionDetails = [
            'order_id' => rand(),
            'gross_amount' => $cartsSubtotal
        ];

        $customer = Auth::user();

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
}
