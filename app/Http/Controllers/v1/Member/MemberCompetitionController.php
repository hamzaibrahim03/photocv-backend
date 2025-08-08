<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Competition\CompetitionRequest;
use App\Services\Member\MemberCompetitionService;

class MemberCompetitionController extends Controller
{
    private $competitionService;

    public function __construct(MemberCompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }

    public function joinCompetition(CompetitionRequest $request)
    {
        return $this->competitionService->joinCompetition($request->validated());
    }

    public function submitCompetitionEntry(CompetitionRequest $request)
    {
        return $this->competitionService->submitCompetitionEntry($request->validated());
    }

    public function joinedCompetition()
    {
        return $this->competitionService->joinedCompetition();
    }
}
