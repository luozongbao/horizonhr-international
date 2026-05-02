<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title_zh_cn'      => ['sometimes', 'required', 'string', 'max:255'],
            'title_en'         => ['sometimes', 'required', 'string', 'max:255'],
            'title_th'         => ['sometimes', 'required', 'string', 'max:255'],
            'content_zh_cn'    => ['nullable', 'string'],
            'content_en'       => ['nullable', 'string'],
            'content_th'       => ['nullable', 'string'],
            'meta_title_zh_cn' => ['nullable', 'string', 'max:255'],
            'meta_title_en'    => ['nullable', 'string', 'max:255'],
            'meta_title_th'    => ['nullable', 'string', 'max:255'],
            'meta_desc_zh_cn'  => ['nullable', 'string', 'max:500'],
            'meta_desc_en'     => ['nullable', 'string', 'max:500'],
            'meta_desc_th'     => ['nullable', 'string', 'max:500'],
            'category'         => ['sometimes', 'required', 'string',
                                   'in:company_news,industry_news,study_abroad,recruitment'],
            'thumbnail'        => ['nullable', 'string', 'max:500'],
            'status'           => ['sometimes', 'required', 'string', 'in:draft,published,archived'],
            'page_id'          => ['nullable', 'integer', 'exists:pages,id'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error'   => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed.', 'errors' => $validator->errors()],
        ], 422));
    }
}
