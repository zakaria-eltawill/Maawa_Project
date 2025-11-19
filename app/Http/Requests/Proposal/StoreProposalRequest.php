<?php

namespace App\Http\Requests\Proposal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'type' => ['required', Rule::in(['ADD', 'EDIT', 'DELETE'])],
        ];

        if ($type === 'ADD') {
            $rules['payload'] = ['required', 'array'];
            $rules['payload.title'] = ['required', 'string'];
            $rules['payload.description'] = ['required', 'string'];
            $rules['payload.city'] = ['required', 'string', 'max:80'];
            $rules['payload.type'] = ['required', Rule::in(['apartment', 'villa', 'chalet'])];
            $rules['payload.price'] = ['required', 'numeric', 'min:0'];
            // Location required for ADD proposals
            $rules['payload.location'] = ['required', 'array'];
            $rules['payload.location.latitude'] = ['required', 'numeric', 'between:-90,90'];
            $rules['payload.location.longitude'] = ['required', 'numeric', 'between:-180,180'];
            // Photos validation - optional array of URLs
            $rules['payload.photos'] = ['sometimes', 'array', 'max:20'];
            $rules['payload.photos.*'] = ['required', 'string', 'url', 'max:500'];
            // Amenities validation - optional array of strings
            $rules['payload.amenities'] = ['sometimes', 'array'];
            $rules['payload.amenities.*'] = ['string', 'max:50'];
        } elseif ($type === 'EDIT') {
            $rules['property_id'] = ['required', 'uuid', 'exists:properties,id'];
            $rules['version'] = ['required', 'integer', 'min:1'];
            $rules['payload'] = ['required', 'array'];
            // Location optional for EDIT proposals
            $rules['payload.location'] = ['sometimes', 'array'];
            $rules['payload.location.latitude'] = ['sometimes', 'numeric', 'between:-90,90'];
            $rules['payload.location.longitude'] = ['sometimes', 'numeric', 'between:-180,180'];
            // Photos validation - optional array of URLs
            $rules['payload.photos'] = ['sometimes', 'array', 'max:20'];
            $rules['payload.photos.*'] = ['required', 'string', 'url', 'max:500'];
            // Amenities validation - optional array of strings
            $rules['payload.amenities'] = ['sometimes', 'array'];
            $rules['payload.amenities.*'] = ['string', 'max:50'];
        } elseif ($type === 'DELETE') {
            $rules['property_id'] = ['required', 'uuid', 'exists:properties,id'];
            $rules['reason'] = ['required', 'string', 'min:3'];
        }

        return $rules;
    }
}
