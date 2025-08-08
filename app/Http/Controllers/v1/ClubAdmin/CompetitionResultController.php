<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubAdmin\CompetitionResultService;

class CompetitionResultController extends Controller
{
    private $competitionResultService;

    public function __construct(CompetitionResultService $competitionResultService)
    {
        $this->competitionResultService = $competitionResultService;
    }

    public function index(Request $request)
    {
        return $this->competitionResultService->index($request);
    }

    public function show($id, Request $request)
    {
        return $this->competitionResultService->show($id, $request);
    }
}
