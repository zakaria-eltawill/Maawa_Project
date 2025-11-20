<?php

namespace App\Http\Requests\Proposal;

use App\Models\Proposal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $proposal = $this->route('id') ? Proposal::find($this->route('id')) : null;
        $proposalType = $proposal ? $proposal->type : null;

        $rules = [
            'type' => ['sometimes', Rule::in(['ADD', 'EDIT', 'DELETE'])],
        ];

        if ($type === 'ADD' || ($type === null && $proposalType === 'ADD')) {
            $rules['payload'] = ['sometimes', 'array'];
            $rules['payload.title'] = ['sometimes', 'string', 'max:200'];
            $rules['payload.description'] = ['sometimes', 'string', 'max:5000'];
            $rules['payload.city'] = ['sometimes', 'string', 'max:80'];
            $rules['payload.type'] = ['sometimes', Rule::in(['apartment', 'villa', 'chalet'])];
            $rules['payload.price'] = ['sometimes', 'numeric', 'min:0'];
            $rules['payload.location'] = ['sometimes', 'array'];
            $rules['payload.location.latitude'] = ['sometimes', 'numeric', 'between:-90,90'];
            $rules['payload.location.longitude'] = ['sometimes', 'numeric', 'between:-180,180'];
            $rules['payload.location.map_url'] = ['sometimes', 'nullable', 'string', 'url', 'max:500'];
            $rules['payload.photos'] = ['sometimes', 'array', 'max:20'];
            $rules['payload.photos.*'] = ['required', 'string', 'url', 'max:500'];
            $rules['payload.amenities'] = ['sometimes', 'array'];
            $rules['payload.amenities.*'] = ['string', 'max:50'];
        } elseif ($type === 'EDIT' || ($type === null && $proposalType === 'EDIT')) {
            $rules['property_id'] = ['sometimes', 'uuid', 'exists:properties,id'];
            $rules['version'] = ['sometimes', 'integer', 'min:1'];
            $rules['payload'] = ['sometimes', 'array'];
            $rules['payload.location'] = ['sometimes', 'array'];
            $rules['payload.location.latitude'] = ['sometimes', 'numeric', 'between:-90,90'];
            $rules['payload.location.longitude'] = ['sometimes', 'numeric', 'between:-180,180'];
            $rules['payload.location.map_url'] = ['sometimes', 'nullable', 'string', 'url', 'max:500'];
            $rules['payload.photos'] = ['sometimes', 'array', 'max:20'];
            $rules['payload.photos.*'] = ['required', 'string', 'url', 'max:500'];
            $rules['payload.amenities'] = ['sometimes', 'array'];
            $rules['payload.amenities.*'] = ['string', 'max:50'];
        } elseif ($type === 'DELETE' || ($type === null && $proposalType === 'DELETE')) {
            $rules['property_id'] = ['sometimes', 'uuid', 'exists:properties,id'];
            $rules['reason'] = ['sometimes', 'string', 'min:3'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'payload.photos.*.url' => 'Each photo must be a valid URL.',
            'payload.photos.max' => 'You may not upload more than 20 photos.',
        ];
    }
}

