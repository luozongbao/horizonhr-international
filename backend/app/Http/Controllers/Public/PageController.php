<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/public/pages
    // All published pages (no pagination — few static pages)
    // ─────────────────────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        $pages = Page::where('status', 'published')
            ->orderBy('order_num')
            ->orderBy('created_at')
            ->get([
                'id', 'slug', 'type', 'order_num',
                'title_zh_cn', 'title_en', 'title_th',
                'meta_title_zh_cn', 'meta_title_en', 'meta_title_th',
                'meta_desc_zh_cn', 'meta_desc_en', 'meta_desc_th',
                'created_at', 'updated_at',
            ]);

        return response()->json([
            'success' => true,
            'data'    => $pages,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/public/pages/{slug}
    // Published page by slug — all language content
    // ─────────────────────────────────────────────────────────────────────────

    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Page not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $page,
        ]);
    }
}
