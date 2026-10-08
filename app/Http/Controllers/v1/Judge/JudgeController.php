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

    public function getEntriesWithScores(Request $request, $competitionId)
    {
        $judgeId = auth()->id();
        return $this->service->getEntriesWithScores($request, $competitionId, $judgeId);
    }

    public function saveScore(Request $request, $entryId)
    {
        $judgeId = auth()->id();

        $validated = $request->validate([
            'score'   => 'required|integer|min:1|max:100',
            'comment' => 'nullable|string',
            'position' => 'nullable|in:1,2,3',
            'is_bookmarked' => 'nullable|boolean',
        ]);

        return $this->service->saveScore($entryId, $judgeId, $validated);
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
