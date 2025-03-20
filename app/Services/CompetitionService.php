<?php
namespace App\Services;

use App\Repositories\CompetitionRepositoryInterface;
use App\Models\Competition;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\DataTables;

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
        return $this->competitionRepository->show( $id );
    }

    public function createCompetition(array $data ): Competition
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
}
