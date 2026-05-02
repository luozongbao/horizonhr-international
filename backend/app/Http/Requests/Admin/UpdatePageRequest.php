<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');

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
            'slug'             => ['nullable', 'string', 'max:255',
                                   "unique:pages,slug,{$id}",
                                   'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'status'           => ['sometimes', 'required', 'string', 'in:draft,published'],
            'type'             => ['sometimes', 'required', 'string', 'in:page,announcement'],
            'order_num'        => ['nullable', 'integer', 'min:0'],
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
