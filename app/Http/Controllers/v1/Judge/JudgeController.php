<?php

namespace App\Http\Controllers\v1\Judge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Judge\CompetitionEntryScoreService;
use App\Services\Judge\JudgeService;

class JudgeController extends Controller
{
    protected $service;
    protected $judgeService;

    public function __construct(CompetitionEntryScoreService $service, JudgeService $judgeService)
    {
        $this->service = $service;
        $this->judgeService = $judgeService;
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

    public function dashboardData(Request $request)
    {
        return $this->judgeService->getDashboardData(auth()->id(), $request);
    }

    public function competitionsForJudge(Request $request, $clubId)
    {
        return $this->judgeService->getCompetitionsForJudge($request, auth()->id(), $clubId);
    }
}
