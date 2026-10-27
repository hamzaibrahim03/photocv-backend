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
        // This endpoint is shared by club_admin (who owns a club via the
        // club() relation) and Secretary (who only joins one via the
        // many-to-many clubs() relation), so fall back to the first joined
        // club when there's no owned one.
        $clubId = auth()->user()->club?->id ?? auth()->user()->clubs->first()?->id;

        return $this->competitionRepository->all( $request, $clubId );
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
