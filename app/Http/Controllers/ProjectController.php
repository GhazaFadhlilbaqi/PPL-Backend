<?php

namespace App\Http\Controllers;

use App\Exports\ProjectRabExport;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Order;
use App\Models\Project;
use App\Models\Province;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class ProjectController extends Controller
{

    public function index(Request $request)
    {
        return $this->getTableFormattedData(
            Project::where('user_id', Auth::user()->id)->with(['province', 'subscription']))
              // Get active order
              ->addColumn('order', function($project) {
                return $project->order()->where('is_active', true)->first();
              })
              ->addColumn('last_opened_at_formatted', function($data) {
                  return $data->last_opened_at ? date('d-m-Y', strtotime($data->last_opened_at)) : 'Belum Pernah di Buka';
              })->addColumn('created_at_formatted', function($data) {
                  return date('d-m-Y', strtotime($data->created_at));
              })->addColumn('expired_at_formatted', function($data) {
                  return date('d-m-Y', strtotime($data->order->expired_at));
              })
              ->make();
    }

    public function store(ProjectRequest $request)
    {

        DB::beginTransaction();

        if (Auth::user()->demo_quota <= 0) {
            DB::rollBack();
            return response()->json([
                'message' => 'Demo project telah habis'
            ]);
        }

        $request->merge([
            'user_id' => Auth::user()->id,
            'province_id' => Province::findByHashid($request->province_id)->id,
            'subscription_id' => 'demo'
        ]);

        $project = Project::create($request->only([
            'user_id', 'name', 'activity', 'job', 'address', 'province_id', 'fiscal_year', 'profit_margin', 'ppn', 'subscription_id'
        ]));

        $user = Auth::user();

        // We assume user has demo quota
        $order = Order::create([
            'order_id' => $this->generateOrderId(),
            'user_id' => $user->id,
            'project_id' => $project->id,
            'is_active' => true,
            'expired_at' => Carbon::now()->subMonth(-1),
            'subscription_id' => 'demo',
            'status' => 'completed',
            'used_at' => Carbon::now(),
            'gross_amount' => 0,
            'payment_method' => '-',
            'type' => 'demo'
        ]);

        $project->subscription_id = $order->subscription_id;
        $project->save();

        $user->demo_quota -= 1;
        $user->save();

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    public function renew(Project $project)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $order = Order::where('user_id', $user->id)->where('used_at', null)->where('project_id', null)->first();

            if ($order) {

                Order::where('user_id', $user->id)->where('project_id', $project->id)->update([
                    'is_active' => false,
                ]);

                $order->project_id = $project->id;
                $order->is_active = true;
                $order->save();

                $project->subscription_id = $order->subscription_id;
                $project->save();

            } else {
                throw new Exception('Tidak dapat renew project, tidak ada order yang bisa di assign ke project.');
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate payment'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Project $project, ProjectUpdateRequest $request)
    {

        if ($project->user_id != Auth::user()->id) return $this->giveUnbelongedAccessResponse();

        $request->merge(['province_id' => Province::findByHashid($request->province_id)->id]);

        $project->update($request->only([
            'name', 'activity', 'job', 'address', 'fiscal_year', 'profit_margin', 'province_id', 'ppn'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    public function updateLastOpenedAt(Project $project)
    {
        $project->last_opened_at = Carbon::now();
        $project->update();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function destroy(Project $project)
    {
        if ($project->user_id != Auth::user()->id) return $this->giveUnbelongedAccessResponse();

        $project->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }

    public function show(Project $project)
    {
        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    public function export(Project $project)
    {
        $projectId = $project->hashidToId($project->hashid);

        return (new ProjectRabExport($projectId))->download('exports.xlsx');
        // In trial mode, create order when user export RAB
        // if (env('APP_USER_TRIAL_MODE')) {
        //     $order = Order::create([
        //         'order_id' => generateRandomOrderId(),
        //         'user_id' => Auth::user()->id,
        //         'project_id' => $projectId,
        //         'gross_amount' => 0,
        //         'status' => 'completed'
        //     ]);
        // }

        // FIXME: SECURITY HOLE ! if somemone unauthorized access this route with knowing project id, then the user might lost his order to export
        // $order = Order::where('project_id', $projectId)->where('status', 'completed')->where('used_at', null)->first();

        // if ($order) {
        //     $order->used_at = Carbon::now();
        //     $order->save();
        // } else {
        //     return abort(403);
        // }

    }

    private function giveUnbelongedAccessResponse()
    {
        return response()->json([
            'status' => 'fail',
            'message' => 'Nice try ! this project ID isn\'t belongs to current user'
        ], 400);
    }

    private function generateOrderId()
    {
        return strtoupper(Str::random(16));
    }
}
