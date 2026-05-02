<?php

namespace App\Http\Requests\Enterprise;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'           => ['sometimes', 'string', 'max:500'],
            'description'     => ['sometimes', 'string'],
            'requirements'    => ['sometimes', 'nullable', 'string'],
            'location'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'salary_min'      => ['sometimes', 'nullable', 'integer', 'min:0'],
            'salary_max'      => ['sometimes', 'nullable', 'integer', 'min:0', 'gte:salary_min'],
            'salary_currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'job_type'        => ['sometimes', 'nullable', 'in:full_time,part_time,contract,internship'],
            'expires_at'      => ['sometimes', 'nullable', 'date', 'after:today'],
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
