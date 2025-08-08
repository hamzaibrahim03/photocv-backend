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
}
