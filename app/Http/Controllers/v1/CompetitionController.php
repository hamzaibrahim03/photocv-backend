<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompetitionService;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Requests\Competition\UpdateCompetitionRequest;

class CompetitionController extends Controller
{
    private $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }

    public function index( Request $request )
    {
        return response()->json($this->competitionService->allCompetitions( $request ));
    }

    public function show( $id ) {
        return response()->json( $this->competitionService->showCompetition( $id ) );
    }

    public function store(StoreCompetitionRequest $request)
    {
        try {
            return response()->json(
                $this->competitionService->createCompetition($request->except('images'), $request->file('images'))
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateCompetitionRequest $request, $id)
    {
        try {
            return response()->json( $this->competitionService->updateCompetition( $id, $request->except('images'), $request->file('images') ) );
        } catch ( \Exception $e ) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy( $id )
    {
        return response()->json($this->competitionService->deleteCompetition( $id ) );
    }
}
