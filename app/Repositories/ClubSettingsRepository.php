<?php

namespace App\Repositories;

use App\Models\ClubSetting;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\Club;
use App\Http\Responses\ClubSettingResponse;

class ClubSettingsRepository implements ClubSettingsRepositoryInterface
{
    public function all( )
    {
        try {
            $club = Club::where('user_id', auth()->id())->first();

            if (!$club) {
                return ClubSettingResponse::error('No club found for the current user.', 404);
            }

            $clubSettings = ClubSetting::first();

            $totalMemberCount = User::whereHas('clubs', function ($query) use ($club) {
                $query->where('club_id', $club->id);
            })
            ->count();

            // Upcoming event (closest future event)
            $upcomingEvent = Event::where('club_id', $club->id)
                ->whereDate('event_date', '>=', now())
                ->orderBy('event_date', 'asc')
                ->first();

            $upcoming = $upcomingEvent
                ? ['remaining_days' => Carbon::now()->startOfDay()->diffInDays(Carbon::parse($upcomingEvent->event_date)->startOfDay(), false)]
                : null;

            return ClubSettingResponse::success('Settings retrieved successfully.', [
                'settings' => $clubSettings,
                'total_members' => $totalMemberCount,
                'upcoming_event_days_count' => $upcoming,
            ]);

        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return ClubSettingResponse::error($e->getMessage(), $statusCode);
        }
    }

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
