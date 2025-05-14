<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Http\Resources\FeatureResource;

class FeaturesController extends Controller
{
    public function index()
    {
        $features = Feature::all();
        return response()->json([
            'status' => 'success',
            'data' => [
                "features" => FeatureResource::collection($features)
            ]
        ]);
    }
}
