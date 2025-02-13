<?php

namespace App\Http\Controllers\Order;

use App\Helpers\ProjectHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\Subscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $orders = Auth::user()->order()->with(['project', 'subscription'])->orderBy('created_at', 'DESC')->get();

        $orders = $orders->map(function($data) {
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
            return $this->handleSuccessfulOrder($order);
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

    private function handleSuccessfulOrder(Order $order) {
        $project = $order->type == 'create'
            ? $this->makeProject($order)
            : Project::find($order->project_id);

        if ($order->type != 'create') {
            // Update subscription id
            $project->subscription_id = $order->subscription_id;
            $project->save();

            // Increment expire date
            $subscription = Subscription::where('id', $order->subscription_id)->first();
            if ($subscription) {
                $monthDuration = ['MONTHLY' => 1, 'QUARTERLY' => 3];

                // Check for renew same package
                $latestOrder = Order::where('id', '!=', $order->id)->latest();
                $order->expired_at = Carbon::parse($latestOrder ? $latestOrder->expired_at : $order->expired_at)
                    ->addMonths($monthDuration[$subscription->subscription_type])
                    ->toDateString();
                $order->save();
            }
        }

        $this->markOrderAsComplete($project, $order);
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
        $input = $order->order_id . '200' . ($order->gross_amount .'.00') . (env('MIDTRANS_MODE') == 'sandbox' ? env('MIDTRANS_SERVER_KEY_DEVELOPMENT') : env('MIDTRANS_SERVER_KEY_PRODUCTION'));
        return openssl_digest($input, 'sha512');
    }

    // Make project from ProjectTemporary
    private function makeProject($order)
    {
        try {
            DB::beginTransaction();

            $projectTemporary = $order->projectTemporary;

            $project = Project::create([
                'user_id' => $projectTemporary->user_id,
                'name' => $projectTemporary->name,
                'activity' => $projectTemporary->activity,
                'job' => $projectTemporary->job,
                'address' => $projectTemporary->address,
                'province_id' => $projectTemporary->province_id,
                'fiscal_year' => $projectTemporary->fiscal_year,
                'profit_margin' => $projectTemporary->profit_margin,
                'ppn' => $projectTemporary->ppn,
                'subscription_id' => $order->subscription_id,
            ]);

            $projectTemporary->delete();

            DB::commit();

            return $project;

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
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

    private function markOrderAsComplete(Project $project, Order $order)
    {
        Order::where('project_id', $project->id)->update([
            'is_active' => false,
        ]);
        $order->project_id = $project->id;
        $order->status = 'completed';
        $order->is_active = true;
        $order->save();
    }
}
