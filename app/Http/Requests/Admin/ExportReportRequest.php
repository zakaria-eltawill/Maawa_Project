<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['bookings', 'occupancy', 'revenue'])],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Report type is required.',
            'type.in' => 'Invalid report type. Must be bookings, occupancy, or revenue.',
            'from.required' => 'Start date is required.',
            'from.date' => 'Start date must be a valid date.',
            'to.required' => 'End date is required.',
            'to.date' => 'End date must be a valid date.',
            'to.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}

