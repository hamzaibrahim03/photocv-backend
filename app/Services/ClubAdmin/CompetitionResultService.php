<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\CompetitionResultRepositoryInterface;

class CompetitionResultService
{
    private $competitionResultRepository;

    public function __construct(CompetitionResultRepositoryInterface $competitionResultRepository)
    {
        $this->competitionResultRepository = $competitionResultRepository;
    }

    public function index($request)
    {
        return $this->competitionResultRepository->index($request, auth()->user());
    }

    public function show($id, $request)
    {
        return $this->competitionResultRepository->getCompetitionEntriesWithScores($id, $request, auth()->user());
    }

    public function assignPositionsAndPublish($data, $competitionId)
    {
        return $this->competitionResultRepository->assignPositionsAndPublish($data, $competitionId);
    }

    public function getAllPublishedResults()
    {
        return $this->competitionResultRepository->getAllPublishedResults(request('club_id'), true);
    }
}
