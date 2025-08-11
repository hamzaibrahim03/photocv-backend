<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use App\Services\ClubAdmin\ClubSettingService;
use App\Http\Requests\ClubSettings\StoreClubSettingsRequest;

class ClubSettingsController extends Controller
{
    private $clubSettingService;

    public function __construct(ClubSettingService $clubSettingService)
    {
        $this->clubSettingService = $clubSettingService;
    }

    public function index( )
    {
        return $this->clubSettingService->showSettings( );
    }

    public function store(StoreClubSettingsRequest $request)
    {
        return $this->clubSettingService->saveClubSettings($request->except('logo'), $request->file('logo'), $request->file('club_banner'), $request->id );
    }
}
