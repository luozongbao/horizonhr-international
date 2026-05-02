<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * GET /api/admin/announcements
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Announcement::query()->orderBy('created_at', 'desc');

        if ($request->filled('is_published')) {
            $query->where('is_published', filter_var($request->query('is_published'), FILTER_VALIDATE_BOOLEAN));
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

    /**
     * POST /api/admin/announcements
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_zh_cn'    => ['required', 'string', 'max:500'],
            'title_en'        => ['required', 'string', 'max:500'],
            'title_th'        => ['required', 'string', 'max:500'],
            'content_zh_cn'   => ['nullable', 'string'],
            'content_en'      => ['nullable', 'string'],
            'content_th'      => ['nullable', 'string'],
            'type'            => ['nullable', 'string', 'max:100'],
            'target'          => ['nullable', 'in:all,students,enterprises'],
            'is_published'    => ['boolean'],
            'published_at'    => ['nullable', 'date'],
            'expires_at'      => ['nullable', 'date'],
        ]);

        if (!empty($validated['is_published']) && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $announcement = Announcement::create($validated);

        return response()->json(['success' => true, 'data' => $announcement], 201);
    }

    /**
     * GET /api/admin/announcements/{id}
     */
    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        return response()->json(['success' => true, 'data' => $announcement]);
    }

    /**
     * PUT /api/admin/announcements/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title_zh_cn'    => ['sometimes', 'required', 'string', 'max:500'],
            'title_en'        => ['sometimes', 'required', 'string', 'max:500'],
            'title_th'        => ['sometimes', 'required', 'string', 'max:500'],
            'content_zh_cn'   => ['nullable', 'string'],
            'content_en'      => ['nullable', 'string'],
            'content_th'      => ['nullable', 'string'],
            'type'            => ['nullable', 'string', 'max:100'],
            'target'          => ['nullable', 'in:all,students,enterprises'],
            'is_published'    => ['boolean'],
            'published_at'    => ['nullable', 'date'],
            'expires_at'      => ['nullable', 'date'],
        ]);

        $announcement->update($validated);

        return response()->json(['success' => true, 'data' => $announcement->fresh()]);
    }

    /**
     * DELETE /api/admin/announcements/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        Announcement::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Announcement deleted.']);
    }
}
