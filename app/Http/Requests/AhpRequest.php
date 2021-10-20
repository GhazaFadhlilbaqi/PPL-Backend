<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AhpRequest extends FormRequest
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
            'id' => ['required_with:name', Rule::unique('ahps', 'id')->ignore($this->ahp, 'id')],
            'name' => 'required_with:id',
            'Pw' => 'sometimes|numeric',
            'Cp' => 'sometimes|numeric',
            'A' => 'sometimes|numeric',
            'W' => 'sometimes|numeric',
            'B' => 'sometimes|numeric',
            'i' => 'sometimes|numeric',
            'U1' => 'sometimes|numeric',
            'U2' => 'sometimes|numeric',
            'Mb' => 'sometimes|numeric',
            'Ms' => 'sometimes|numeric',
            'MP' => 'sometimes|numeric',
        ];
    }
}
