<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:10240', // 10MB in kilobytes
            ],
            'folder' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/', // Only alphanumeric, dash, underscore
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'The file field is required.',
            'file.image' => 'The file must be an image.',
            'file.mimes' => 'Invalid file format. Only JPEG, PNG, and WebP images are allowed.',
            'file.max' => 'The file may not be greater than 10MB.',
            'folder.regex' => 'The folder name may only contain letters, numbers, dashes, and underscores.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert file size from bytes to kilobytes for validation
        if ($this->hasFile('file')) {
            $file = $this->file('file');
            $sizeInKB = $file->getSize() / 1024;
            
            // If file exceeds 10MB, add custom error
            if ($sizeInKB > 10240) {
                $this->merge([
                    'file_size_kb' => $sizeInKB,
                ]);
            }
        }
    }
}

