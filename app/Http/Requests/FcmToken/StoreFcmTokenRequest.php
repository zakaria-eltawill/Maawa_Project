<?php

namespace App\Http\Requests\FcmToken;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFcmTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'min:10'],
            'platform' => ['required', Rule::in(['android', 'ios', 'web'])],
        ];
    }
}
