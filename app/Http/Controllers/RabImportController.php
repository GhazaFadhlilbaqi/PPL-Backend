<?php

namespace App\Http\Controllers;

use App\Imports\RabExcelImport;
use App\Models\Project;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RabImportController extends Controller
{
    public function import(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new RabExcelImport($project), $request->file('file'));
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
