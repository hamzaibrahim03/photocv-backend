<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompetitionEntryScoreService;

class JudgeController extends Controller
{
    protected $service;

    public function __construct(CompetitionEntryScoreService $service)
    {
        $this->service = $service;
    }

    public function getEntriesWithScores($competitionId)
    {
        $judgeId = auth()->id();
        $result = $this->service->getEntriesWithScores($competitionId, $judgeId);

        return response()->json($result);
    }

    public function saveScore(Request $request, $entryId)
    {
        $judgeId = auth()->id();

        $validated = $request->validate([
            'score'   => 'required|integer|min:1|max:100',
            'comment' => 'nullable|string'
        ]);

        $score = $this->service->saveScore($entryId, $judgeId, $validated);

        return response()->json([
            'message' => 'Score saved successfully',
            'data'    => $score
        ]);
    }
}
