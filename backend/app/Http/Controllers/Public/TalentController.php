<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TalentCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    private const VALID_STATUSES = ['visible', 'featured'];

    /**
     * GET /api/public/talent
     * List public talent cards. Only cards with at least one approved resume are shown.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        // Restrict to requested status or all public statuses
        $statuses = [$request->query('status', null)];
        $statuses = array_filter($statuses, fn ($s) => in_array($s, self::VALID_STATUSES, true));
        if (empty($statuses)) {
            $statuses = self::VALID_STATUSES;
        }

        $query = TalentCard::with('student')
            ->whereIn('status', $statuses)
            // Only show cards where the student has at least one approved resume
            ->whereHas('student.resume', fn ($q) => $q->where('status', 'approved'))
            ->orderByRaw("CASE status WHEN 'featured' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc');

        if ($request->filled('nationality')) {
            $query->whereHas('student', fn ($q) => $q->where('nationality', $request->query('nationality')));
        }

        if ($request->filled('major')) {
            $query->where('major', 'like', '%' . $request->query('major') . '%');
        }

        if ($request->filled('education')) {
            $query->where('education', $request->query('education'));
        }

        if ($request->filled('language')) {
            $lang = $request->query('language');
            $query->whereJsonContains('languages', $lang);
        }

        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(fn (TalentCard $tc) => $this->formatCard($tc));

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/public/talent/{id}
     * Single public talent card.
     */
    public function show(int $id): JsonResponse
    {
        $card = TalentCard::with('student')
            ->whereIn('status', self::VALID_STATUSES)
            ->whereHas('student.resume', fn ($q) => $q->where('status', 'approved'))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatCard($card),
        ]);
    }

    private function formatCard(TalentCard $tc): array
    {
        // languages is cast to array of {language, level} objects — format as readable strings
        $rawLangs = $tc->languages ?? [];
        $languages = array_values(array_map(function ($l) {
            if (is_array($l)) {
                $lang  = $l['language'] ?? '';
                $level = $l['level']    ?? '';
                return $level ? "{$lang} ({$level})" : $lang;
            }
            return (string) $l;
        }, $rawLangs));

        // education is a plain string — normalize to translation key and wrap in EducationItem array
        $eduString = $tc->education ?? '';
        $eduLower  = strtolower($eduString);
        $eduKey    = match (true) {
            str_contains($eduLower, 'bachelor') => 'bachelor',
            str_contains($eduLower, 'master')   => 'master',
            str_contains($eduLower, 'phd') || str_contains($eduLower, 'doctor') => 'phd',
            default                              => $eduString,
        };
        $education = $eduString ? [[
            'degree'      => $eduString,
            'institution' => $tc->university ?? '',
            'year'        => null,
        ]] : [];

        return [
            'id'              => $tc->id,
            'name'            => $tc->display_name,   // frontend expects 'name'
            'display_name'    => $tc->display_name,
            'photo_url'       => $tc->student?->avatar ?? null,  // frontend expects 'photo_url'
            'avatar'          => $tc->student?->avatar ?? null,
            'nationality'     => $tc->student?->nationality,
            'bio'             => $tc->student?->bio ?? null,
            'major'           => $tc->major,
            'education_level' => $eduKey,              // normalized key for t() lookup
            'education'       => $education,           // EducationItem[] for the modal timeline
            'university'      => $tc->university,
            'languages'       => $languages,           // string[] for chips
            'skills'          => $tc->skills ?? [],    // string[]
            'job_intention'   => $tc->job_intention,
            'status'          => $tc->status,
            'updated_at'      => $tc->updated_at,
        ];
    }
}
