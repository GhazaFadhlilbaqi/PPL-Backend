<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'activity' => 'required',
            'job' => 'required',
            'address' => 'required',
            'province_id' => 'required',
            'fiscal_year' => 'required|numeric',
            'profit_margin' => 'required|numeric',
            'ppn' => 'required|numeric|max:100',
            'subscription_id' => 'required',
        ];
    }
}
