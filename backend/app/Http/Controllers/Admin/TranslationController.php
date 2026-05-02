<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTranslationRequest;
use App\Http\Requests\Admin\UpdateTranslationRequest;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationController extends Controller
{
    /**
     * Paginated list of translation keys with all language values.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Language::query();

        if ($request->filled('search')) {
            $query->where('key', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('key')->paginate(50),
        ]);
    }

    /**
     * Get a single translation key.
     */
    public function show(string $key): JsonResponse
    {
        $translation = Language::where('key', $key)->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $translation,
        ]);
    }

    /**
     * Create a new translation key.
     */
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = Language::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $translation,
        ], 201);
    }

    /**
     * Update a translation key's values.
     */
    public function update(UpdateTranslationRequest $request, string $key): JsonResponse
    {
        $translation = Language::where('key', $key)->firstOrFail();
        $translation->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $translation->fresh(),
        ]);
    }

    /**
     * Delete a translation key.
     */
    public function destroy(string $key): JsonResponse
    {
        Language::where('key', $key)->firstOrFail()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Translation key deleted.',
        ]);
    }

    /**
     * Export all translations as a downloadable JSON file.
     */
    public function export(): StreamedResponse
    {
        $translations = Language::all()
            ->mapWithKeys(fn (Language $item) => [
                $item->key => [
                    'en'    => $item->en,
                    'zh_cn' => $item->zh_cn,
                    'th'    => $item->th,
                ],
            ]);

        $payload = [
            'version'      => '1.0',
            'exported_at'  => now()->toIso8601String(),
            'translations' => $translations,
        ];

        $filename = 'translations_' . now()->format('Y-m-d') . '.json';
        $json     = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(
            static function () use ($json) { echo $json; },
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Import translations from an uploaded JSON file.
     * Upserts each key (creates if absent, updates if exists).
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json', 'max:5120'],
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data    = json_decode($content, true);

        if (! isset($data['translations']) || ! is_array($data['translations'])) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_FORMAT',
                    'message' => 'Invalid translations file format. Expected { "translations": { ... } }.',
                ],
            ], 422);
        }

        $count = 0;
        foreach ($data['translations'] as $key => $values) {
            Language::updateOrCreate(
                ['key' => $key],
                [
                    'en'    => $values['en']    ?? null,
                    'zh_cn' => $values['zh_cn'] ?? null,
                    'th'    => $values['th']    ?? null,
                ]
            );
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$count} translation keys.",
            'data'    => ['count' => $count],
        ]);
    }
}
