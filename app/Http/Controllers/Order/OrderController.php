<?php

namespace App\Http\Controllers\Order;

use App\Helpers\OrderHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $orders = Auth::user()->order()->with(['project', 'subscription'])->orderBy('created_at', 'DESC')->get();

        $orders = $orders->map(function ($data) {
            $data->formatted_date = date('d-m-Y H:i', strtotime($data->created_at));
            $data->formatted_gross_amount = number_format($data->gross_amount);
            $data->formatted_expired_at = $data->expired_at ? date('d-m-Y', strtotime($data->expired_at)) : '-';
            $data->is_expired = Carbon::create($data->expired_at)->lt(Carbon::now());
            return $data;
        });

        return response()->json([
            'status' => 'success',
            'data' => compact('orders'),
        ]);
    }

    public function notify(Request $request)
    {
        Log::info('Incoming payment notification at ' . Carbon::now()->format('Y-m-d H:i:s'));

        if (!$request->has('order_id')) {
            return;
        }
        $order = Order::where('order_id', $request->order_id)->first();
        $order->payment_method = $this->determinePaymentMethod($request);

        // Handle unverified order
        if ($request->signature_key != $this->generateSignature($order)) {
            return;
        }

        // Handle successful order
        if ($request->transaction_status == 'capture' || $request->status_code == '200') {
            OrderHelper::setOrderAsSuccessful($order);
            return response()->json([
                'status' => 'ok'
            ]);
        }

        // Handle pending or cancelled order
        $order->status = $request->status_code ? 'pending' : 'cancelled';
        $order->save();
        return response()->json([
            'status' => 'ok'
        ]);
    }

    public function orderStatusByProject(Project $project)
    {
        $orders = Auth::user()->order()->where('project_id', $project->hashidToId($project->hashid))->where('status', 'completed')->where('used_at', null)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'quotasLeft' => env('APP_USER_TRIAL_MODE') ? 1 : $orders->count()
            ]
        ]);
    }

    private function generateSignature($order)
    {
        $input = $order->order_id . '200' . ($order->gross_amount . '.00') . (env('MIDTRANS_MODE') == 'sandbox' ? env('MIDTRANS_SERVER_KEY_DEVELOPMENT') : env('MIDTRANS_SERVER_KEY_PRODUCTION'));
        return openssl_digest($input, 'sha512');
    }

    private function determinePaymentMethod(Request $request)
    {

        $paymentMethodStr = '';

        switch ($request->payment_type) {
            case 'qris':
                $paymentMethodStr = 'QRIS | ' . strtoupper($request->acquirer);
                break;
            case 'credit_card':
                $paymentMethodStr = 'Credit Card | ' . strtoupper($request->bank);
                break;
            case 'bank_transfer':
                $paymentMethodStr = 'Bank Transfer / VA';
            default:
                $paymentMethodStr = 'Unidentified Payment Method';
        }

        return $paymentMethodStr;
    }
}
