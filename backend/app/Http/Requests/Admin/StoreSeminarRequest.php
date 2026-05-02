<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSeminarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_zh_cn'    => ['required', 'string', 'max:500'],
            'title_en'        => ['required', 'string', 'max:500'],
            'title_th'        => ['required', 'string', 'max:500'],
            'desc_zh_cn'      => ['nullable', 'string'],
            'desc_en'         => ['nullable', 'string'],
            'desc_th'         => ['nullable', 'string'],
            'speaker_name'    => ['nullable', 'string', 'max:255'],
            'speaker_title'   => ['nullable', 'string', 'max:255'],
            'speaker_bio'     => ['nullable', 'string'],
            'speaker_avatar'  => ['nullable', 'string', 'max:2048'],
            'thumbnail'       => ['nullable', 'string', 'max:2048'],
            'stream_url'      => ['nullable', 'string', 'max:2048'],
            'target_audience' => ['required', 'in:students,enterprises,both'],
            'permission'      => ['required', 'in:public,registered'],
            'starts_at'       => ['required', 'date', 'after:now'],
            'duration_min'    => ['nullable', 'integer', 'min:1', 'max:1440'],
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
