<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUniversityRequest;
use App\Http\Requests\Admin\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\JsonResponse;

class UniversityController extends Controller
{
    /**
     * POST /api/admin/universities
     */
    public function store(StoreUniversityRequest $request): JsonResponse
    {
        $university = University::create($request->validated());

        return response()->json(['success' => true, 'data' => $university], 201);
    }

    /**
     * PUT /api/admin/universities/{id}
     */
    public function update(UpdateUniversityRequest $request, int $id): JsonResponse
    {
        $university = University::findOrFail($id);
        $university->update($request->validated());

        return response()->json(['success' => true, 'data' => $university->fresh()]);
    }

    /**
     * DELETE /api/admin/universities/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        University::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'University deleted.']);
    }
}
