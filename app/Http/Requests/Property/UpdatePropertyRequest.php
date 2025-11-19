<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'string', 'max:5000'],
            'city' => ['sometimes', 'string', 'max:80'],
            'type' => ['sometimes', Rule::in(['apartment', 'villa', 'chalet'])],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'location' => ['sometimes', 'array'],
            'location.latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'location.longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'location.map_url' => ['sometimes', 'nullable', 'string', 'url', 'max:500'],
            'amenities' => ['sometimes', 'array'],
            'amenities.*' => ['string', 'max:50'],
            'photos' => ['sometimes', 'array', 'max:20'],
            'photos.*' => ['required', 'string', 'url', 'max:500'],
            'unavailable_dates' => ['sometimes', 'array'],
            'unavailable_dates.*' => ['date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'The title may not be greater than 200 characters.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'type.in' => 'The type must be one of: apartment, villa, chalet.',
            'price.min' => 'The price must be at least 0.',
            'location.latitude.between' => 'Latitude must be between -90 and 90.',
            'location.longitude.between' => 'Longitude must be between -180 and 180.',
            'photos.max' => 'You may not upload more than 20 photos.',
            'photos.*.url' => 'Each photo must be a valid URL.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If location is provided, ensure it's an array
        if ($this->has('location') && !is_array($this->location)) {
            $this->merge(['location' => []]);
        }
    }
}

