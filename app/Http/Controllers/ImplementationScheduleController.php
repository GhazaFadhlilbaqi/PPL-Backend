<?php

namespace App\Http\Controllers;

use App\Models\ImplementationSchedule;
use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItem;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;
use Vinkla\Hashids\Facades\Hashids;

class ImplementationScheduleController extends Controller
{
    public function index(Project $project)
    {
        return response()->json([
            'status' => 'success',
            'data' => $project->implementationSchedule,
        ]);
    }

    public function updateProjectDuration(Project $project, Request $request)
    {
        try {
            DB::beginTransaction();
            $project->implementation_duration = $request->implementation_duration;
            $project->save();

            if ($project->implementation_duration && $project->implementationSchedule()->count() > 0) {
                foreach ($project->implementationSchedule as $is) {
                    if ($is->end_of_week >= $request->implementation_duration) {
                        if ($is->start_of_week != $is->end_of_week) {
                            $is->delete();
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengubah durasi proyek'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Project $project, Request $request)
    {
        try {
            DB::beginTransaction();

            ImplementationSchedule::where('project_id', $project->hashidToId($project->hashid))->where('rab_item_id', Hashids::decode($request->rab_item_id)[0])->delete();

            foreach ($request->implementation_schedules as $is) {
                ImplementationSchedule::create([
                    'project_id' => $project->hashidToId($project->hashid),
                    'rab_item_id' => Hashids::decode($request->rab_item_id)[0],
                    'start_of_week' => $is['start'],
                    'end_of_week' => $is['end'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengubah jadwal pelaksanaan'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengubah jadwal pengerjaan proyek',
                'err' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Project $project, RabItem $rabItem)
    {
        try {
            ImplementationSchedule::where('rab_item_id', $rabItem->hashidToId($rabItem->hashid))->where('project_id', $project->hashidToId($project->hashid))->delete();
            return response()->json([
                'message' => 'Berhasil menghapus jadwal pelaksanaan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // public function destroy(Project $project, ImplementationSchedule $implementationSchedule)
    // {
    //     try {
    //         $implementationSchedule->delete();
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Berhasil menghapus jadwal pelaksanaan'
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage(),
    //         ]);
    //     }
    // }

    public function getProjectDuration(Project $project)
    {
        return response()->json([
            'message' => 'Berhasil get detail durasi proyek',
            'data' => [
                'projectDuration' => $project->implementation_duration,
            ],
        ]);
    }

    public function showSCurveContent(Project $project)
    {
        $path = storage_path('app/public/' . 'Kurva S - ' . $project->name . '.png');
        $htmlContent = $this->generateSCurveHtml($project);
        Browsershot::html($htmlContent)
            ->setChromePath(env('CHROME_PATH', ''))
            ->showBackground()
            ->landscape(true)
            ->deviceScaleFactor(2)
            ->fullPage()
            ->setOption('headless', true)
            ->setEnvironmentOptions([
                'CHROME_CONFIG_HOME' => storage_path('app/chrome/.config')
            ])
            ->save($path);
        return response()->file($path, [
            'Content-Type' => 'image/png',
        ])->deleteFileAfterSend(true);
    }

    public function downloadSCurve(Project $project)
    {
        $path = storage_path('app/public/' . 'Kurva S - ' . $project->name . '.pdf');
        $htmlContent = $this->generateSCurveHtml($project);
        Browsershot::html($htmlContent)
            ->format('A3')
            ->setChromePath(env('CHROME_PATH', ''))
            ->showBackground()
            ->setOption('headless', true)
            ->setEnvironmentOptions([
                'CHROME_CONFIG_HOME' => storage_path('app/chrome/.config')
            ])
            ->save($path);
        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function generateSCurveHtml(Project $project)
    {

        function calculateEffort($rabItem, $totalPrice)
        {
            return number_format((($rabItem['volume'] * $rabItem['price']) / $totalPrice) * 100, 2);
        }

        $budgetPlans = Rab::where('project_id', $project->hashidToId($project->hashid))
            ->with(['rabItemHeader.rabItem.unit', 'rabItemHeader.rabItem.implementationSchedule', 'rabItem.unit'])
            ->get()
            ->map(function ($rab) {
                $rabItems = $rab->rabItem->where('hashed_rab_item_header_id', null)->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'volume' => $item->volume,
                        'price' => $item->price,
                        'unit_name' => $item->unit->name,
                        'implementation_schedule' => count($item->implementationSchedule) > 0 ? new Collection([
                            'start_of_week' => $item->implementationSchedule[0]->start_of_week,
                            'end_of_week' => $item->implementationSchedule[0]->end_of_week
                        ]) : null,
                        'weeks_efforts' => new Collection()
                    ];
                });

                $rabItemHeaders = $rab->rabItemHeader->map(function ($header) {
                    return [
                        'name' => $header->name,
                        'rab_items' => $header->rabItem->map(function ($item) {
                            return [
                                'name' => $item->name,
                                'volume' => $item->volume,
                                'price' => $item->price,
                                'unit_name' => $item->unit->name,
                                'implementation_schedule' => count($item->implementationSchedule) > 0  ? new Collection([
                                    'start_of_week' => $item->implementationSchedule[0]->start_of_week,
                                    'end_of_week' => $item->implementationSchedule[0]->end_of_week
                                ]) : null,
                                'weeks_efforts' => new Collection()
                            ];
                        }),
                    ];
                });

                return [
                    'name' => $rab->name,
                    'created_at' => $rab->created_at,
                    'updated_at' => $rab->updated_at,
                    'hashid' => $rab->hashid,
                    'hashed_project_id' => $rab->hashed_project_id,
                    'rab_item_headers' => $rabItemHeaders,
                    'rab_items' => $rabItems,
                ];
            });

        // Calculate Rab Item Total Price
        $total_pretax_price = 0;
        foreach ($budgetPlans as $budgetPlan) {
            foreach ($budgetPlan['rab_items'] as $rab_item) {
                $total_pretax_price += ($rab_item['volume'] * $rab_item['price']);
            }
            foreach ($budgetPlan['rab_item_headers'] as $rab_item_header) {
                foreach ($rab_item_header['rab_items'] as $rab_item) {
                    $total_pretax_price += ($rab_item['volume'] * $rab_item['price']);
                }
            }
        }

        // Calculate Rab Item Effort
        $total_effort = 0;
        $budgetPlans = $budgetPlans->map(function ($budgetPlan) use ($project, $total_pretax_price, &$total_effort) {

            // Setup Weekly Effort for each RAB Item
            $budgetPlan['rab_items'] = $budgetPlan['rab_items']->map(function ($rab_item) use ($project, $total_pretax_price, &$total_effort) {
                $rab_item['effort'] = calculateEffort($rab_item, $total_pretax_price);
                $total_effort += $rab_item['effort'];

                $total_weeks = 0;
                if ($rab_item['implementation_schedule'] !== null) {
                    $total_weeks = ($rab_item['implementation_schedule']['end_of_week'] - $rab_item['implementation_schedule']['start_of_week']) + 1;
                }

                for ($i = 0; $i < $project['implementation_duration']; $i++) {
                    if (
                        $rab_item['implementation_schedule'] != null
                        && ($i + 1) >= $rab_item['implementation_schedule']['start_of_week']
                        && ($i + 1) <= $rab_item['implementation_schedule']['end_of_week']
                    ) {
                        $rab_item['weeks_efforts']->push($rab_item['effort'] / $total_weeks);
                    } else {
                        $rab_item['weeks_efforts']->push(null);
                    }
                }
                return $rab_item;
            })->toArray();

            // Setup Weekly Effort for each RAB Header Item
            $budgetPlan['rab_item_headers'] = $budgetPlan['rab_item_headers']->map(function ($rab_item_header) use ($project, $total_pretax_price, &$total_effort) {
                $rab_item_header['rab_items'] = $rab_item_header['rab_items']->map(function ($rab_item) use ($project, $total_pretax_price, &$total_effort) {
                    $rab_item['effort'] = calculateEffort($rab_item, $total_pretax_price);
                    $total_effort += $rab_item['effort'];

                    $total_weeks = 0;
                    if ($rab_item['implementation_schedule'] !== null) {
                        $total_weeks = ($rab_item['implementation_schedule']['end_of_week'] - $rab_item['implementation_schedule']['start_of_week']) + 1;
                    }

                    for ($i = 0; $i < $project['implementation_duration']; $i++) {
                        if (
                            $rab_item['implementation_schedule'] != null
                            && ($i + 1) >= $rab_item['implementation_schedule']['start_of_week']
                            && ($i + 1) <= $rab_item['implementation_schedule']['end_of_week']
                        ) {
                            $rab_item['weeks_efforts']->push($rab_item['effort'] / $total_weeks);
                        } else {
                            $rab_item['weeks_efforts']->push(null);
                        }
                    }

                    return $rab_item;
                })->toArray();
                return $rab_item_header;
            })->toArray();

            return $budgetPlan;
        });

        // Setup Weekly Efforts
        $total_weekly_efforts = new Collection();
        $total_accumulative_weekly_efforts = new Collection();
        for ($i = 0; $i < $project['implementation_duration']; $i++) {
            $total_weekly_effort = 0.0;
            foreach ($budgetPlans as $budgetPlan) {
                foreach ($budgetPlan['rab_items'] as $rab_item) {
                    $total_weekly_effort += $rab_item['weeks_efforts'][$i];
                }

                foreach ($budgetPlan['rab_item_headers'] as $rab_item_header) {
                    foreach ($rab_item_header['rab_items'] as $rab_item) {
                        $total_weekly_effort += $rab_item['weeks_efforts'][$i];
                    }
                }
            }

            $total_weekly_efforts->push($total_weekly_effort);
            $accumulative_weekly_effort = $i == 0
                ? $total_weekly_effort
                : $total_accumulative_weekly_efforts[$i - 1] + $total_weekly_effort;
            $total_accumulative_weekly_efforts->push($accumulative_weekly_effort);
        }

        $data = new Collection([
            'company' => Auth::user()->company,
            'project_name' => $project->name,
            'fiscal_year' => $project->fiscal_year,
            'implementation_duration' => $project->implementation_duration,
            'total_pretax_price' => $total_pretax_price,
            'total_effort' => $total_effort,
            'works' => $budgetPlans,
            'total_weekly_efforts' => $total_weekly_efforts,
            'total_accumulative_weekly_efforts' => $total_accumulative_weekly_efforts
        ]);

        return view('s-curve', ['data' => $data])->render();
    }
}
