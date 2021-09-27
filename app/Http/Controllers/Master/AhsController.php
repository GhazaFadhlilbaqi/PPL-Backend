<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AhsRequest;
use App\Models\Ahs;
use Illuminate\Http\Request;

class AhsController extends Controller
{

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Ahs::all(),
        ]);
    }

    public function store(AhsRequest $request)
    {
        Ahs::create($request->only(['id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => Ahs::all()
        ]);
    }
}
