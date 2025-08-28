<?php

namespace App\Services\Judge;

use App\Repositories\Judge\CompetitionEntryScoreRepositoryInterface;

class CompetitionEntryScoreService
{
    protected $repository;

    public function __construct(CompetitionEntryScoreRepositoryInterface $repositoryInterface)
    {
        $this->repository = $repositoryInterface;
    }

    public function getEntriesWithScores($request, $competitionId, $judgeId)
    {
        return $this->repository->getJudgeScoresForCompetition($request, $competitionId, $judgeId);
    }

    public function saveScore($entryId, $judgeId, array $data)
    {
        return $this->repository->saveOrUpdateScore($entryId, $judgeId, $data);
    }
}
