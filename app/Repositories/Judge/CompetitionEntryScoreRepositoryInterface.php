<?php

namespace App\Repositories\Judge;

interface CompetitionEntryScoreRepositoryInterface
{
    public function getJudgeScoresForCompetition($request, $competitionId, $judgeId);
    public function saveOrUpdateScore($entryId, $judgeId, array $data);
}
