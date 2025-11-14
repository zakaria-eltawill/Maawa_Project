<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_lat' => $this->input('location_lat') === '' ? null : $this->input('location_lat'),
            'location_lng' => $this->input('location_lng') === '' ? null : $this->input('location_lng'),
            'location_url' => $this->filled('location_url') ? trim($this->input('location_url')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['apartment', 'villa', 'chalet'])],
            'price' => ['required', 'numeric', 'min:0'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'location_url' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['required', 'exists:users,id'],
            'amenities' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'image', 'max:5120'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['string'],
        ];
    }
}
