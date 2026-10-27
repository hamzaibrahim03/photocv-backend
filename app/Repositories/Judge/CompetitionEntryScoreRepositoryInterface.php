<?php

namespace App\Repositories\Judge;

interface CompetitionEntryScoreRepositoryInterface
{
    public function getJudgeScoresForCompetition($request, $competitionId, $judgeId);
    public function saveOrUpdateScore($entryId, $judgeId, array $data);
    public function recomputeAwardPositions(int $competitionId, int $judgeId): void;
}
