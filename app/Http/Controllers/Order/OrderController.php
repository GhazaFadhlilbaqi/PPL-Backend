<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $orders = Auth::user()->order()->select(['created_at', 'gross_amount', 'used_at', 'order_id', 'status', 'project_id'])->with('project')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 'success',
            'data' => compact('orders'),
        ]);
    }

    public function notify(Request $request)
    {
        if ($request->has('order_id')) {
            /**
             * Security concern for notify
             *
             * - Signature check
             * - Challenge response
             *
             */
            $order = Order::where('order_id', $request->order_id)->first();

             // Signature Check
             if ($request->signature_key == $this->generateSignature($order)) {
                // Check if it's captured or not
                if ($request->transaction_status == 'capture') {
                    $order->status = 'completed';
                    $order->save();
                } else {
                    switch ($request->status_code) {
                        case '200':
                            $order->status = 'completed';
                        break;
                        case '201':
                            $order->status = 'pending';
                        break;
                        case '202':
                            $order->status = 'cancelled';
                        break;
                    }
                    $order->save();
                }
             }

            return response()->json([
                'status' => 'ok'
            ]);
        }
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
        $input = $order->order_id . '200' . ($order->gross_amount .'.00') . env('MIDTRANS_SERVER_KEY');
        return openssl_digest($input, 'sha512');
    }
}
