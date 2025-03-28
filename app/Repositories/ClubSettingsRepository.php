<?php

namespace App\Repositories;

use App\Models\ClubSetting;
use App\Http\Responses\ClubSettingResponse;

class ClubSettingsRepository implements ClubSettingsRepositoryInterface
{

    /**
     * Store or update club settings.
     */
    public function save(array $data, $logo = null, $clubBanner = null, $id = null)
    {

        try {
            if ($logo) {
                $data['logo'] = $logo->store('club_logos', 'public');
            }
            if ($clubBanner) {
                $data['club_banner'] = $clubBanner->store('club_banners', 'public');
            }
    
            // Store or update club settings
            $clubSetting = ClubSetting::updateOrCreate(
                ['id' => $id],
                $data
            );
    
            return ClubSettingResponse::success('Club Settings Saved Successfully.', $clubSetting, 201);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return ClubSettingResponse::error($e->getMessage(), $statusCode);
        }

        
    }

}
