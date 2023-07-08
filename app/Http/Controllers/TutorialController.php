<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorialController extends Controller
{
    public function index()
    {
        try {
            $tutorials = Auth::user()->tutorial;
            return response()->json([
                'status' => 'success',
                'data' => $tutorials,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        try {

            $tutorial = Auth::user()->tutorial;

            $tutorial->update([
                $request->key => $request->val,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => ''
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
