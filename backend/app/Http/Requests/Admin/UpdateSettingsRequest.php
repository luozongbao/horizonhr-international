<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings'             => ['required', 'array'],
            'settings.smtp_port'   => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:65535'],
            'settings.session_timeout' => ['sometimes', 'nullable', 'numeric', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ],
        ], 422));
    }
}
