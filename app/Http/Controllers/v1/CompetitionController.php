<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompetitionService;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Responses\CompetitionResponse;

class CompetitionController extends Controller
{
    private $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }
    public function index(Request $request)
    {
        return $this->competitionService->allCompetitions($request);
    }

    public function show( $id ) {
        return $this->competitionService->showCompetition( $id );
    }

    public function store(StoreCompetitionRequest $request)
    {
        return $this->competitionService->createCompetition($request->validated());
    }

    public function update(StoreCompetitionRequest $request, $id)
    {
        return $this->competitionService->updateCompetition($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->competitionService->deleteCompetition($id);
    }
}
