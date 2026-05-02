<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/admin/pages
    // All pages including drafts; supports ?status and ?type filters
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Page::query()->orderBy('order_num')->orderBy('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', $search)
                  ->orWhere('title_zh_cn', 'like', $search)
                  ->orWhere('title_th', 'like', $search)
                  ->orWhere('slug', 'like', $search);
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/admin/pages/{id}
    // Single page by id (includes drafts)
    // ─────────────────────────────────────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        $page = Page::find($id);

        if (! $page) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Page not found.'],
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $page]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/admin/pages
    // Create a new page. Slug auto-generated from title_en if not provided.
    // Note: content fields support HTML — admin-only input, trusted source.
    // ─────────────────────────────────────────────────────────────────────────

    public function store(StorePageRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title_en']);
        }

        $data['order_num'] ??= 0;

        $page = Page::create($data);

        return response()->json([
            'success' => true,
            'data'    => $page,
            'message' => 'Page created successfully.',
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/admin/pages/{id}
    // ─────────────────────────────────────────────────────────────────────────

    public function update(UpdatePageRequest $request, int $id): JsonResponse
    {
        $page = Page::find($id);

        if (! $page) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Page not found.'],
            ], 404);
        }

        $data = $request->validated();

        // If slug is being cleared, auto-generate from title_en
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            $titleEn = $data['title_en'] ?? $page->title_en;
            $data['slug'] = $this->generateSlug($titleEn, $id);
        }

        $page->update($data);

        return response()->json([
            'success' => true,
            'data'    => $page->fresh(),
            'message' => 'Page updated successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/admin/pages/{id}
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        $page = Page::find($id);

        if (! $page) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Page not found.'],
            ], 404);
        }

        // Prevent deletion if posts are still referencing this page
        if ($page->posts()->exists()) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'HAS_POSTS',
                    'message' => 'Cannot delete page while it has associated posts. Reassign or delete the posts first.',
                ],
            ], 422);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Page deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function generateSlug(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        while (true) {
            $query = Page::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
