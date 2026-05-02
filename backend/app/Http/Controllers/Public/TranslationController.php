<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    private const VALID_LANGS = ['en', 'zh_cn', 'th'];

    /**
     * Return translations.
     *
     * Without ?lang — returns all keys with every locale:
     *   { "key": { "en": "...", "zh_cn": "...", "th": "..." }, ... }
     *
     * With ?lang=en — returns flat key-value for that locale only:
     *   { "key": "value", ... }
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->query('lang');

        if ($lang && in_array($lang, self::VALID_LANGS, true)) {
            $data = Language::all()
                ->mapWithKeys(fn (Language $item) => [$item->key => (string) ($item->{$lang} ?? '')])
                ->toArray();
        } else {
            $data = Language::all()
                ->mapWithKeys(fn (Language $item) => [
                    $item->key => [
                        'en'    => $item->en,
                        'zh_cn' => $item->zh_cn,
                        'th'    => $item->th,
                    ],
                ])
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
