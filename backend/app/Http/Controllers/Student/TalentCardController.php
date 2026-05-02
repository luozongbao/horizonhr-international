<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateTalentCardRequest;
use App\Models\TalentCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalentCardController extends Controller
{
    /**
     * GET /api/student/talent-card
     * Return own talent card. Creates a blank one if it does not exist yet.
     */
    public function show(Request $request): JsonResponse
    {
        $student    = $request->user()->student;
        $talentCard = TalentCard::firstOrCreate(
            ['student_id' => $student->id],
            ['display_name' => $student->name, 'status' => 'hidden']
        );

        return response()->json([
            'success' => true,
            'data'    => $talentCard,
        ]);
    }

    /**
     * PUT /api/student/talent-card
     */
    public function update(UpdateTalentCardRequest $request): JsonResponse
    {
        $student    = $request->user()->student;
        $talentCard = TalentCard::firstOrCreate(
            ['student_id' => $student->id],
            ['display_name' => $student->name, 'status' => 'hidden']
        );

        $talentCard->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $talentCard->fresh(),
        ]);
    }
}
