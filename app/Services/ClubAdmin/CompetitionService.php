<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\CompetitionRepositoryInterface;

class CompetitionService
{
    private $competitionRepository;

    public function __construct(CompetitionRepositoryInterface $competitionRepository)
    {
        $this->competitionRepository = $competitionRepository;
    }

    public function allCompetitions( $request )
    {
        return $this->competitionRepository->all( $request );
    }

    public function showCompetition( $id ) {
        return $this->competitionRepository->show( $id, auth()->id() );
    }

    public function createCompetition(array $data )
    {
        return $this->competitionRepository->create($data );
    }

    public function updateCompetition(int $id, array $data)
    {
        return $this->competitionRepository->update($id, $data);
    }

    public function deleteCompetition( $id )
    {
        return $this->competitionRepository->delete($id);
    }

    public function getCompetitionExtras( $request )
    {
        return $this->competitionRepository->getCompetitionExtras( $request );
    }

    public function getCompetitionResults()
    {
        return $this->competitionRepository->getCompetitionResults();
    }
}
