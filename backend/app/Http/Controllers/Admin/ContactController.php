<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * GET /api/admin/contacts
     * Filter by status (unread|read|replied). Paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Contact::query()->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
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
     * GET /api/admin/contacts/{id}
     */
    public function show(int $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        // Auto-mark as read on first admin view
        if ($contact->status === 'unread') {
            $contact->update(['status' => 'read']);
        }

        return response()->json(['success' => true, 'data' => $contact->fresh()]);
    }

    /**
     * PUT /api/admin/contacts/{id}/status
     * Update status: read | replied
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:read,replied'],
        ]);

        $contact = Contact::findOrFail($id);
        $data    = ['status' => $request->input('status')];

        if ($request->input('status') === 'replied') {
            $data['replied_at'] = now();
        }

        $contact->update($data);

        return response()->json(['success' => true, 'data' => $contact->fresh()]);
    }

    /**
     * POST /api/admin/contacts/{id}/reply
     * Mark as replied and optionally store a reply note.
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        $contact->update([
            'status'     => 'replied',
            'replied_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $contact->fresh()]);
    }

    /**
     * DELETE /api/admin/contacts/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        Contact::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Contact deleted.']);
    }
}
