<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_zh_cn'       => ['sometimes', 'required', 'string', 'max:500'],
            'name_en'          => ['sometimes', 'required', 'string', 'max:500'],
            'name_th'          => ['sometimes', 'required', 'string', 'max:500'],
            'logo'             => ['nullable', 'string', 'max:2048'],
            'cover_image'      => ['nullable', 'string', 'max:2048'],
            'location'         => ['nullable', 'string', 'max:500'],
            'location_city'    => ['nullable', 'string', 'max:255'],
            'location_region'  => ['nullable', 'string', 'max:255'],
            'website'          => ['nullable', 'url', 'max:2048'],
            'description'      => ['nullable', 'string'],
            'majors'           => ['nullable', 'array'],
            'majors.*'         => ['string', 'max:255'],
            'program_types'    => ['nullable', 'array'],
            'program_types.*'  => ['string', 'max:100'],
            'established_year' => ['nullable', 'integer', 'min:1000', 'max:2099'],
            'ranking'          => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ],
        ], 422));
    }
}
