<?php

namespace App\Http\Requests;

use App\Rules\OldPassword;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $rules = [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required',
            'job' => 'required'
        ];

        if ($this->hasFile('photo-update')) {
            $rules = [
                'photo-update' => 'mimes:png,jpg,svg,bmp,jpeg|max:2048',
            ];
        }

        if ($this->has('email')) {
            $rules = [
                'email' => 'required|email|unique:users,email'
            ];
        }

        if ($this->has('password')) {
            $rules = [
                'old_password' => ['required', new OldPassword],
                'password' => 'required|min:6',
                'password_confirmation' => 'required_with:password|same:password'
            ];
        }

        return $rules;
    }
}
