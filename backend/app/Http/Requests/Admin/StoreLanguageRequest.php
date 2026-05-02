<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:10', 'unique:language_settings,code', 'regex:/^[a-z][a-z0-9_]*$/'],
            'name'        => ['required', 'string', 'max:100'],
            'native_name' => ['required', 'string', 'max:100'],
            'flag'        => ['nullable', 'string', 'max:10'],
            'is_active'   => ['sometimes', 'boolean'],
            'position'    => ['nullable', 'integer', 'min:1'],
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
