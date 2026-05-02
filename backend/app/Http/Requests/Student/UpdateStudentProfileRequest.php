<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:50'],
            'birth_date'  => ['sometimes', 'nullable', 'date'],
            'gender'      => ['sometimes', 'nullable', 'in:male,female,other'],
            'address'     => ['sometimes', 'nullable', 'string', 'max:1000'],
            'bio'         => ['sometimes', 'nullable', 'string', 'max:2000'],
            'prefer_lang' => ['sometimes', 'in:zh_cn,en,th'],
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
