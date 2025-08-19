<?php

namespace App\Repositories;

interface CompetitionEntryScoreRepositoryInterface
{
    public function getJudgeScoresForCompetition($competitionId, $judgeId);
    public function saveOrUpdateScore($entryId, $judgeId, array $data);
}
