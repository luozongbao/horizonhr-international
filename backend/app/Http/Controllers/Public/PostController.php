<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/public/posts
    // Paginated list of published posts; filters: category, page_id, search
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 15)), 100);

        $query = Post::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('page_id')) {
            $query->where('page_id', (int) $request->query('page_id'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', $search)
                  ->orWhere('title_zh_cn', 'like', $search)
                  ->orWhere('title_th', 'like', $search);
            });
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginated->items(),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/public/posts/{id}
    // Single published post — increments view_count atomically
    // ─────────────────────────────────────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        $post = Post::where('id', $id)
            ->where('status', 'published')
            ->first();

        if (! $post) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Post not found.'],
            ], 404);
        }

        // Atomic increment to avoid race conditions on high-traffic posts
        Post::where('id', $id)->increment('view_count');

        return response()->json([
            'success' => true,
            'data'    => $post,
        ]);
    }
}
