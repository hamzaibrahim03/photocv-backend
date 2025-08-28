<?php

namespace App\Repositories\Judge;

interface JudgeRepositoryInterface
{
    public function getJudgePostingInformation($judgeId);
    public function getUpcomingCompetitionsForJudge($judgeId);
    public function getClubsWithUpcomingCompetitions($judgeId, $request);
    public function getAllRecentJudgingByUser($judgeId);
    public function getCompetitionsForJudgeInClub($request, $judgeId, $clubId);
}