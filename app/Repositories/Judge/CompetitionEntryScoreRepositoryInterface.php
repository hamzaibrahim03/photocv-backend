<?php

namespace App\Repositories\Judge;

interface CompetitionEntryScoreRepositoryInterface
{
    public function getJudgeScoresForCompetition($competitionId, $judgeId);
    public function saveOrUpdateScore($entryId, $judgeId, array $data);
}
