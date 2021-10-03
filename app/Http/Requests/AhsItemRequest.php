<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AhsItemRequest extends FormRequest
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
            'section' => 'required|in:labor,ingredients,tools,others',
            'ahs_itemable_id' => 'required',
            'ahs_itemable_type' => 'required',
        ];
    }
}
