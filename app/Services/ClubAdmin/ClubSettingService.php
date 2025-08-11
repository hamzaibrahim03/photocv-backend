<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\ClubSettingsRepositoryInterface;

class ClubSettingService
{
    private $clubSettingRepository;

    public function __construct(ClubSettingsRepositoryInterface $clubSettingRepository)
    {
        $this->clubSettingRepository = $clubSettingRepository;
    }

    public function showSettings( )
    {
        return $this->clubSettingRepository->all( );
    }

    public function saveClubSettings(array $data, $logo, $clubBanner, $id)
    {
        return $this->clubSettingRepository->save($data, $logo, $clubBanner, $id);
    }
}
