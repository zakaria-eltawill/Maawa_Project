<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:80'],
            'phone_number' => [
                'sometimes',
                'string',
                'regex:/^09[0-9]{8}$/',
                "unique:users,phone_number,{$userId}"
            ],
            'region' => ['sometimes', 'string', 'max:100'],

            // Password change rules
            'current_password' => ['required_with:password', 'string'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must be 10 digits and start with 09 (e.g., 0920206878)',
            'current_password.required_with' => 'Current password is required when changing password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}

