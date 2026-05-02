<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInterviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:500'],
            'student_id'    => ['required', 'integer', 'exists:students,id'],
            'scheduled_at'  => ['required', 'date', 'after:now'],
            'duration'      => ['nullable', 'integer', 'min:15', 'max:480'],
            'job_id'        => ['nullable', 'integer', 'exists:jobs,id'],
            'enterprise_id' => ['nullable', 'integer', 'exists:enterprises,id'],
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
