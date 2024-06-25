<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterRabCategoryRequest;
use App\Models\MasterRabCategory;
use Exception;
use Illuminate\Http\Request;

class MasterRabCategoryController extends Controller
{
    public function index()
    {
        $masterRabCategories = MasterRabCategory::with('masterRab')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'masterRabCategories' => $masterRabCategories
            ]
        ]);
    }

    public function store(MasterRabCategoryRequest $request)
    {
        try {
            MasterRabCategory::create($request->only(['name']));
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambah data kategori RAB'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(MasterRabCategory $masterRabCategory)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'masterRabCategory' => $masterRabCategory,
            ]
        ]);
    }
    
    public function destroy(MasterRabCategory $masterRabCategory)
    {
        try {
            $masterRabCategory->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menghapus data RAB'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
