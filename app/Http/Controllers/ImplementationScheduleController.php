<?php

namespace App\Http\Controllers;

use App\Models\ImplementationSchedule;
use App\Models\Project;
use App\Models\RabItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}
