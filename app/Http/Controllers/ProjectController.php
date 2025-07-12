<?php

namespace App\Http\Controllers;

use App\Enums\AhsSectionEnum;
use App\Exports\ProjectRabExport;
use App\Helpers\ProjectHelper;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\FeatureResource;
use App\Models\CustomAhs;
use App\Models\CustomItemPrice;
use App\Models\ItemPrice;
use App\Models\Order;
use App\Models\Project;
use App\Models\Province;
use App\Models\Rab;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class ProjectController extends Controller
{

    public function index(Request $request)
    {
            $query = Project::where('user_id', Auth::user()->id)
                ->with(['province', 'subscription'])
                ->when($request->query('name'), function ($q, $name) {
                    return $q->where('name', 'like', "%$name%");
                })
                ->orderBy('last_opened_at', 'desc');

            return $this->getTableFormattedData($query)
              // Get active order        
              ->addColumn('order', function($project) {
                return $project->order()->where('is_active', true)->first();
            })
            ->addColumn('last_opened_at_formatted', function ($data) {
                return $data->last_opened_at ? date('d-m-Y', strtotime($data->last_opened_at)) : 'Belum pernah dibuka';
            })->addColumn('created_at_formatted', function ($data) {
                return date('d-m-Y', strtotime($data->created_at));
            })->addColumn('expired_at_formatted', function ($data) {
                return date('d-m-Y', strtotime($data->order->expired_at));
            })->addColumn('subscriptionPrice', function ($data) {
                $subscriptionPrice = $data->order->subscriptionPrice;
                if ($subscriptionPrice == null) return;
                return [
                    'id' => $subscriptionPrice->id,
                    'durationType' => $subscriptionPrice->duration_type,
                    'discountedPrice' => $subscriptionPrice->discounted_price,
                    'minDuration' => $subscriptionPrice->min_duration,
                ];
            })
            ->make();
    }

    public function store_demo(ProjectRequest $request)
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
            'user_id',
            'name',
            'activity',
            'job',
            'address',
            'province_id',
            'fiscal_year',
            'profit_margin',
            'ppn',
            'subscription_id'
        ]));

        $user = Auth::user();
        $order = Order::create([
            'order_id' => $this->generateOrderId(),
            'user_id' => $user->id,
            'project_id' => $project->id,
            'is_active' => true,
            'expired_at' => ProjectHelper::get_expired_date('demo'),
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

    public function update(Project $project, Request $request)
    {
        if ($project->user_id != Auth::user()->id) {
            return $this->giveUnbelongedAccessResponse();
        };
        $validatedRequest = $request->validate([
            'institution_name' => 'required|string|max:255',
            'department_name'  => 'nullable|string|max:255',
            'job_name'         => 'required|string|max:255',
            'address'          => 'required|string|max:255',
            'province_id'      => 'required|string',
            'fiscal_year'      => 'required|integer|min:2020|max:2100',
            'profit_margin'    => 'nullable|numeric|min:0|max:100',
            'ppn'              => 'nullable|numeric|min:0|max:100',
        ]);
        $validatedRequest['name'] = $validatedRequest['job_name'];
        $province = Province::findByHashid($validatedRequest['province_id']);
        if (!$province) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Provinsi tidak ditemukan'
            ], 422);
        }
        $project->update([
            'institution_name' => $validatedRequest['institution_name'],
            'department_name'  => $validatedRequest['department_name'],
            'name'             => $validatedRequest['job_name'],
            'address'          => $validatedRequest['address'],
            'province_id'      => $province->id,
            'fiscal_year'      => $validatedRequest['fiscal_year'],
            'profit_margin'    => $validatedRequest['profit_margin'],
            'ppn'              => $validatedRequest['ppn'],
        ]);
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
        $project['features'] = FeatureResource::collection($project->activeOrder->subscription->features);
        return response()->json([
            'status' => 'success',
            'data' => ['project' =>  $project]
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

    public function getMaterialSummary(Project $project)
    {
        // TODO: GET RAB ITEM INSTEAD OF CUSTOM AHS BCS CUSTOM AHS CAN BE NULL

        // 1) Merge all custom ahs item data into one collection
        $rabs = $project->load(['rab.rabItem.customAhs.customAhsItem.customAhsItemable', 'rab.rabItem.unit'])->rab;
        $rabItems = $rabs->flatMap(function ($rab) {
            return $rab->rabItem;
        });

        // return $rabItems;

        // 2) Check for custom rab item (not related to ahs) & any duplicated ahs item and increment coefficient & price
        $mergedAhsItems = new Collection();
        foreach ($rabItems as $rabItem) {
            if (!isset($rabItem->customAhs)) {
                $mergedAhsItems->push(new Collection([
                    'name' => $rabItem->name,
                    'unit_name' => $rabItem->unit->name,
                    'total_coefficient' => $rabItem->volume,
                    'total_price' => $rabItem->price ?? 0,
                    'section' => null
                ]));
                continue;
            }
            $ahsItems = $rabItem->customAhs->customAhsItem;
            foreach ($ahsItems as $ahsItem) {
                $customAhsItem = $ahsItem->customAhsItemable;
                $mergedAhsItem = $mergedAhsItems->first(function ($mergedAhsItem) use ($customAhsItem) {
                    return $mergedAhsItem['name'] == $customAhsItem->name;
                });
                if (isset($mergedAhsItem)) {
                    $mergedAhsItem['total_coefficient'] = $mergedAhsItem['total_coefficient'] + $ahsItem->coefficient;
                    continue;
                }
                $unitName = $ahsItem->custom_ahs_itemable_type == CustomAhs::class
                    ? $ahsItem->unit->name
                    : $customAhsItem->unit->name;
                $customAhsItemPrice = $customAhsItem->price;
                if ($ahsItem->custom_ahs_itemable_type == CustomAhs::class) {
                    $customAhsItemPrice = $customAhsItem->customAhsItem
                        ->sum(function ($customAhsItem) {
                            return $customAhsItem->customAhsItemable->price ?? 0;
                        });
                }
                $mergedAhsItems->push(new Collection([
                    'name' => $customAhsItem->name,
                    'unit_name' => $unitName,
                    'total_coefficient' => $ahsItem->coefficient,
                    'total_price' => $ahsItem->coefficient * $customAhsItemPrice,
                    'section' => $ahsItem->section
                ]));
            }
        }

        return $mergedAhsItems->sortBy('name')->values()->sortBy(function ($item) {
            return $item['section'] != AhsSectionEnum::LABOR->value && $item['section'] == null;
        })->values();
    }

    function rabItemPrices(Project $project, Request $request) {
        $rabs = Rab::where('project_id', $project->id)
            ->with('rabItem.customAhs.customAhsItem.customAhsItemable')
            ->get(); 
        $mappedRabs = $rabs->map(function ($rab) {
            $mappedRabItems = $rab->rabItem->map(function ($rabItem) {
                return [
                    'volume' => $rabItem->volume,
                    'customAhsName' => $rabItem->customAhs->name,
                    'customAhsItems' => $rabItem->customAhs->customAhsItem->map(function ($customAhsItem) use ($rabItem) {
                        $unitName = $customAhsItem->custom_ahs_itemable_type == CustomAhs::class
                            ? $customAhsItem->unit->name
                            : $customAhsItem->customAhsItemable->unit->name;
                        $customAhsItemPrice = $customAhsItem->customAhsItemable->price;
                        if ( $customAhsItem->custom_ahs_itemable_type == CustomAhs::class) {
                            $customAhsItemPrice = $customAhsItem->customAhsItemable->customAhsItem
                                ->sum(function ($customAhsItem) {
                                    return $customAhsItem->customAhsItemable->price ?? 0;
                                });
                        }
                        return [
                            'name' => $customAhsItem->customAhsItemable->name,
                            'coefficient' => $customAhsItem->coefficient,
                            'price' => $customAhsItemPrice ,
                            'unitName' => $unitName,
                            'totalNeeds' => $rabItem->volume * $customAhsItem->coefficient,
                            'totalPrice' => $customAhsItem->coefficient * $customAhsItemPrice
                        ];
                    })
                ];
            });
            return [
                'name' => $rab->name,
                'rabItems' => $mappedRabItems
            ];
        });
        return [
            'rabs' => $mappedRabs
        ];
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
