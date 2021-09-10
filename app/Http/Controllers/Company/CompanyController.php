<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
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
            'is_active' => count($request->owner->company) <= 0,
        ]);

        $company = Company::create($request->only([
            'user_id', 'name', 'email', 'director_name', 'phone_number', 'is_active'
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
        $company = $company->update($request->only([
            'name', 'email', 'director_name', 'phone_number', 'is_active'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('company'),
        ]);
    }

    public function setActiveCompany(Company $company)
    {
        Company::where('user_id', $company->user_id)->update([
            'is_active' => false
        ]);

        $company->is_active = true;
        $company->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'companies' => Company::where('user_id', $company->user_id)->get(),
                'active_company' => $company,
            ]
        ]);
    }

    public function destroy(Company $company)
    {
        if ($company->is_active) {
            return response()->json([
                'status' => 'fail',
                'message' => 'This company is still active'
            ], 400);
        }

        $company->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }
}
