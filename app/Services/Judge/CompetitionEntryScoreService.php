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

    public function getEntriesWithScores($competitionId, $judgeId)
    {
        $result = $this->repository->getJudgeScoresForCompetition($competitionId, $judgeId);

        // If judge not assigned, just pass the error message forward
        if (! ($result['success'] ?? false)) {
            return $result;
        }

        $entries = $result['entries'];

        return [
            'success'   => true,
            'entries'   => $entries,
            'progress'  => [
                'scored' => $entries->filter(fn($entry) => $entry->scores->isNotEmpty())->count(),
                'total'  => $entries->count(),
            ],
            'positions' => $result['positions']
        ];
    }

    public function saveScore($entryId, $judgeId, array $data)
    {
        return $this->repository->saveOrUpdateScore($entryId, $judgeId, $data);
    }
}
