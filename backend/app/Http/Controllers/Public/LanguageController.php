<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LanguageSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{
    /**
     * Return only active languages ordered by position.
     * Cached in Redis for 1 hour.
     */
    public function activeLanguages(): JsonResponse
    {
        $languages = Cache::remember('languages:active', 3600, function () {
            return LanguageSetting::where('is_active', true)
                ->orderBy('position')
                ->get(['code', 'name', 'native_name', 'flag', 'position']);
        });

        return response()->json([
            'success' => true,
            'data'    => $languages,
        ]);
    }
}
