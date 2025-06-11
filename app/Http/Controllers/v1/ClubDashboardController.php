<?php

namespace App\Http\Controllers\v1;

use App\Services\ClubDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClubDashboardController extends Controller
{
    protected $clubDashboardService;

    public function __construct(ClubDashboardService $clubDashboardService)
    {
        $this->clubDashboardService = $clubDashboardService;
    }

    public function getDashboardData(Request $request)
    {
        $clubId = $request->user()->club->id;

        $dashboardData = $this->clubDashboardService->getDashboardData($clubId);

        return response()->json([
            'status' => 'success',
            'data' => $dashboardData
        ]);
    }
}
