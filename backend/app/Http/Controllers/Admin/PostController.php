<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\OssService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/admin/posts
    // All posts including drafts; supports filters + pagination
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Post::query()
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

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
    // GET /api/admin/posts/{id}
    // Single post by id (includes drafts)
    // ─────────────────────────────────────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Post not found.'],
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $post]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/admin/posts
    // Note: content fields support HTML (rich text) — admin-only, trusted input.
    // ─────────────────────────────────────────────────────────────────────────

    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);

        return response()->json([
            'success' => true,
            'data'    => $post,
            'message' => 'Post created successfully.',
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/admin/posts/{id}
    // ─────────────────────────────────────────────────────────────────────────

    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Post not found.'],
            ], 404);
        }

        $data = $request->validated();

        // Auto-set published_at if transitioning to published state
        if (isset($data['status']) && $data['status'] === 'published' && ! $post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return response()->json([
            'success' => true,
            'data'    => $post->fresh(),
            'message' => 'Post updated successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/admin/posts/{id}
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Post not found.'],
            ], 404);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Post deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/admin/posts/{id}/publish
    // Publish a draft post
    // ─────────────────────────────────────────────────────────────────────────

    public function publish(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Post not found.'],
            ], 404);
        }

        if ($post->status === 'published') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'ALREADY_PUBLISHED', 'message' => 'Post is already published.'],
            ], 422);
        }

        $post->update([
            'status'       => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => ['id' => $post->id, 'status' => 'published', 'published_at' => $post->published_at],
            'message' => 'Post published successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/admin/posts/{id}/unpublish
    // Revert a published post to draft
    // ─────────────────────────────────────────────────────────────────────────

    public function unpublish(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Post not found.'],
            ], 404);
        }

        if ($post->status !== 'published') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_PUBLISHED', 'message' => 'Post is not currently published.'],
            ], 422);
        }

        $post->update(['status' => 'draft']);

        return response()->json([
            'success' => true,
            'data'    => ['id' => $post->id, 'status' => 'draft'],
            'message' => 'Post unpublished.',
        ]);
    }

    /**
     * POST /api/admin/posts/media-upload
     * Upload an image for the rich text editor. Returns the public URL.
     */
    public function mediaUpload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'],
        ]);

        $oss = app(OssService::class);
        $key = $oss->uploadAvatar(
            $request->file('file'),
            'posts/media/' . now()->format('Y/m')
        );
        $url = $oss->publicUrl($key);

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ]);
    }
}
