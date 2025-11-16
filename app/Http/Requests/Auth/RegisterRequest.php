<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['tenant', 'owner'])],
            'phone_number' => [
                'required',
                'string',
                'regex:/^09[0-9]{8}$/',
                'unique:users,phone_number'
            ],
            'region' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must be 10 digits and start with 09 (e.g., 0920206878)',
        ];
    }
}
