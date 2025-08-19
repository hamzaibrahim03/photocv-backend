<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use App\Services\ClubAdmin\CompetitionGlobalSettingService;
use App\Http\Requests\Competition\StoreCompetitionGlobalSettingRequest;

class CompetitionGlobalSettingController extends Controller
{
    private $competitionGlobalSettingService;

    public function __construct(CompetitionGlobalSettingService $competitionGlobalSettingService)
    {
        $this->competitionGlobalSettingService = $competitionGlobalSettingService;
    }

    public function index()
    {
        return response()->json($this->competitionGlobalSettingService->getSettings());
    }

    public function store(StoreCompetitionGlobalSettingRequest $request)
    {
        try {
            $setting = $this->competitionGlobalSettingService->saveSettings($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Global settings saved successfully',
                'data' => $setting,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
