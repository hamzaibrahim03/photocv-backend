<?php

namespace App\Repositories\ClubAdmin;

use App\Models\CompetitionGlobalSetting;

class CompetitionGlobalSettingRepository implements CompetitionGlobalSettingRepositoryInterface
{
    public function getFirst($clubId = null)
    {
        if(!$clubId) {
            $clubId = auth()->user()->club->id;
        }
        return CompetitionGlobalSetting::with([
            'competitionType',
            'judgingType',
            'resultMethod',
            'votingMethod',
            'competitionTheme',
            'competitionCategory'
        ])->where('club_id', $clubId)->first();
    }

    public function createOrUpdate(array $data)
    {
        $settings = CompetitionGlobalSetting::first();

        $data['club_id'] = auth()->user()->club->id;

        if ($settings) {
            $settings->update($data);
        } else {
            $settings = CompetitionGlobalSetting::create($data);
        }

        return $settings;
    }
}
