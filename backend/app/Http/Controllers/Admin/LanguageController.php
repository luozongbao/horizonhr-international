<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLanguageRequest;
use App\Http\Requests\Admin\UpdateLanguageRequest;
use App\Models\LanguageSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{
    /**
     * Return ALL language settings (active + inactive), ordered by position.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => LanguageSetting::orderBy('position')->get(),
        ]);
    }

    /**
     * Add a new language.
     */
    public function store(StoreLanguageRequest $request): JsonResponse
    {
        $language = LanguageSetting::create($request->validated());

        Cache::forget('languages:active');

        return response()->json([
            'success' => true,
            'data'    => $language,
        ], 201);
    }

    /**
     * Update a language by code.
     */
    public function update(UpdateLanguageRequest $request, string $code): JsonResponse
    {
        $language = LanguageSetting::where('code', $code)->firstOrFail();
        $language->update($request->validated());

        Cache::forget('languages:active');

        return response()->json([
            'success' => true,
            'data'    => $language->fresh(),
        ]);
    }

    /**
     * Remove a language — the default 'en' language cannot be deleted.
     */
    public function destroy(string $code): JsonResponse
    {
        if ($code === 'en') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'CANNOT_DELETE_DEFAULT',
                    'message' => 'Cannot delete the default language.',
                ],
            ], 422);
        }

        LanguageSetting::where('code', $code)->firstOrFail()->delete();

        Cache::forget('languages:active');

        return response()->json([
            'success' => true,
            'message' => 'Language removed.',
        ]);
    }
}
