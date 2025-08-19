<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\CompetitionGlobalSettingRepository;

class CompetitionGlobalSettingService
{
    private $competitionGlobalSettingRepository;

    public function __construct(CompetitionGlobalSettingRepository $competitionGlobalSettingRepository)
    {
        $this->competitionGlobalSettingRepository = $competitionGlobalSettingRepository;
    }

    public function getSettings()
    {
        return $this->competitionGlobalSettingRepository->getFirst();
    }

    public function saveSettings(array $data)
    {
        return $this->competitionGlobalSettingRepository->createOrUpdate($data);
    }
}
