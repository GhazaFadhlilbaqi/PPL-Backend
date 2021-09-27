<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;

class ProvinceController extends Controller
{
    public function index()
    {

        $provinces = Province::all();

        return response()->json([
            'status' => 'success',
            'data' => compact('provinces'),
        ]);
    }
}
