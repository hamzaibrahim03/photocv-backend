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
    public function all( $userId = null )
    {
        try {
            $club = Club::where('user_id', $userId ?? auth()->id())->first();

            if (!$club) {
                return ClubSettingResponse::error('No club found for the current user.', 404);
            }

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

            $contactData = User::where('id', $userId ?? auth()->id())->first();
            $club['phone'] = $contactData->phone;
            $club['address'] = $contactData->address;

            return ClubSettingResponse::success('Settings retrieved successfully.', [
                'settings' => $club,
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
            // Extract only phone and address for the user update
            $user = auth()->user();

            if ($logo) {
                $data['logo'] = $logo->store('club_logos', 'public');
            }

            if ($clubBanner) {
                $data['club_banner'] = $clubBanner->store('club_banners', 'public');
            }

            // Update user's phone and address if provided
            $user->update([
                'phone' => $data['phone'] ?? $user->phone,
                'address' => $data['address'] ?? $user->address,
            ]);

            // Remove user-specific fields from data before saving to Club
            unset($data['phone'], $data['address']);

            // Handle about_img
            if ($data['about_img']) {
                $aboutImg = $data['about_img'];
                $data['about_img'] = $aboutImg->store('uploads/clubs/about', 'public');
            }

            // Handle footer_img
            if ($data['footer_img']) {
                $footerImg = $data['footer_img'];
                $data['footer_img'] = $footerImg->store('uploads/clubs/footer', 'public');
            }

            // Update or create club settings
            $clubSetting = Club::updateOrCreate(
                ['id' => $user->club->id],
                $data
            );

            return ClubSettingResponse::success('Club Settings Saved Successfully.', $clubSetting, 201);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return ClubSettingResponse::error($e->getMessage(), $statusCode);
        }

    }
}
