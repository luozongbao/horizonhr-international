<?php

namespace App\Http\Requests\Enterprise;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEnterpriseProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'company_name'  => ['sometimes', 'string', 'max:255'],
            'industry'      => ['sometimes', 'nullable', 'string', 'max:100'],
            'scale'         => ['sometimes', 'nullable', 'string', 'max:50'],
            'description'   => ['sometimes', 'nullable', 'string', 'max:5000'],
            'website'       => ['sometimes', 'nullable', 'url', 'max:500'],
            'address'       => ['sometimes', 'nullable', 'string', 'max:1000'],
            'contact_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'prefer_lang'   => ['sometimes', 'in:zh_cn,en,th'],
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
