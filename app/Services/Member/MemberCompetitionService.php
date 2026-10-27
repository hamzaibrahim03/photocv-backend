<?php
namespace App\Services\Member;

use App\Repositories\Member\MemberCompetitionRepositoryInterface;

class MemberCompetitionService
{
    private $competitionRepository;

    public function __construct(MemberCompetitionRepositoryInterface $competitionRepository)
    {
        $this->competitionRepository = $competitionRepository;
    }

    public function joinCompetition( $data )
    {
        return $this->competitionRepository->joinCompetition( $data );
    }

    public function joinedCompetition()
    {
        return $this->competitionRepository->getJoinedCompetition();
    }

    public function submitCompetitionEntry($data){
        return $this->competitionRepository->submitCompetitionEntry($data);
    }

    public function getCompetitionEntries($compId)
    {
        return $this->competitionRepository->getCompetitionEntries($compId);
    }

    public function updateCompetitionEntry($entryId, $data)
    {
        return $this->competitionRepository->updateCompetitionEntry($entryId, $data);
    }

    public function deleteCompetitionEntry($entryId)
    {
        return $this->competitionRepository->deleteCompetitionEntry($entryId);
    }
}
