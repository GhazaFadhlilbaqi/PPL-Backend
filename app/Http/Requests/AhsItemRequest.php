<?php

namespace App\Http\Requests;

use App\Enums\AhsSectionEnum;
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
        $ahsSections = [
            AhsSectionEnum::LABOR->value,
            AhsSectionEnum::INGREDIENTS->value,
            AhsSectionEnum::TOOLS->value,
            AhsSectionEnum::OTHERS->value
        ];
        return [
            'section' => 'required|in:'.implode(',', $ahsSections),
            'ahs_itemable_id' => 'required',
            'ahs_itemable_type' => 'required',
        ];
    }
}
