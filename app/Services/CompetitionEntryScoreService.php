<?php

namespace App\Services;

use App\Repositories\CompetitionEntryScoreRepositoryInterface;

class CompetitionEntryScoreService
{
    protected $repository;

    public function __construct(CompetitionEntryScoreRepositoryInterface $repositoryInterface)
    {
        $this->repository = $repositoryInterface;
    }

    public function getEntriesWithScores($competitionId, $judgeId)
    {
        $entries = $this->repository->getJudgeScoresForCompetition($competitionId, $judgeId);

        return [
            'entries' => $entries,
            'progress' => [
                'scored' => $entries->filter(fn($entry) => $entry->scores->isNotEmpty())->count(),
                'total'  => $entries->count()
            ]
        ];
    }

    public function saveScore($entryId, $judgeId, array $data)
    {
        return $this->repository->saveOrUpdateScore($entryId, $judgeId, $data);
    }
}
