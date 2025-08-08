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
        return $this->competitionResultRepository->index($request);
    }

    public function show($id, $request)
    {
        return $this->competitionResultRepository->show($id, $request);
    }
}
