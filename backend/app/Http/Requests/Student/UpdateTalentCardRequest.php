<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTalentCardRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'display_name'    => ['sometimes', 'string', 'max:255'],
            'major'           => ['sometimes', 'nullable', 'string', 'max:200'],
            'education'       => ['sometimes', 'nullable', 'string', 'max:100'],
            'university'      => ['sometimes', 'nullable', 'string', 'max:500'],
            'languages'       => ['sometimes', 'nullable', 'array'],
            'skills'          => ['sometimes', 'nullable', 'array'],
            'work_experience' => ['sometimes', 'nullable', 'array'],
            'job_intention'   => ['sometimes', 'nullable', 'string', 'max:500'],
            'status'          => ['sometimes', 'in:hidden,visible,featured'],
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
