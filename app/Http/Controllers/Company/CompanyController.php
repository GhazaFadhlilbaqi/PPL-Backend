<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CompanyController extends Controller
{

    const PATH_COMPANY_PROFILE_PICTURE = '/storage/uploads/company/profile-picture/';

    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'companies' => $request->user()->company,
            ],
        ]);
    }

    public function store(CompanyRequest $request)
    {

        $request->merge([
            'user_id' => $request->owner->id,
        ]);

        $company = Company::create($request->only([
            'user_id', 'name', 'email', 'director_name', 'phone_number', 'address', 'picture',
        ]));

        return response()->json([
            'status' => 'success',
            'data' => [
                'company' => $company
            ]
        ]);
    }

    public function update(Company $company, CompanyRequest $request)
    {

        # Check if user updated the picture
        if ($request->hasFile('update_picture')) {

            $randomName = 'picture-' . Str::random(10) . '.' . $request->file('update_picture')->getClientOriginalExtension();
            $request->file('update_picture')->move(public_path(self::PATH_COMPANY_PROFILE_PICTURE), $randomName);

            # Delete current company if the name is not default
            if (!str_starts_with($company->picture, 'default_company') && File::exists(public_path(self::PATH_COMPANY_PROFILE_PICTURE . $company->picture))) {
                File::delete(public_path(self::PATH_COMPANY_PROFILE_PICTURE . $company->picture));
            }

            $request->merge(['picture' => $randomName]);
        }

        $company->update($request->only([
            'name', 'email', 'director_name', 'address', 'phone_number', 'picture'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('company'),
        ]);
    }
}
