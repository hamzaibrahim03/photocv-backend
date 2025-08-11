<?php

namespace App\Repositories\ClubAdmin;

use App\Models\ClubSetting;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\ClubSeason;
use App\Http\Responses\ClubSettingResponse;

class ClubSettingsRepository implements ClubSettingsRepositoryInterface
{
    public function all($userId = null)
    {
        try {
            $user = User::find($userId ?? auth()->id());

            if (!$user) {
                return ClubSettingResponse::error('User not found.', 404);
            }

            $club = $user->club;

            if (!$club) {
                return ClubSettingResponse::error('No club found for the current user.', 404);
            }

            $clubSetting = $club->setting;

            // Total members in the club
            $totalMemberCount = User::whereHas('clubs', function ($query) use ($club) {
                $query->where('club_id', $club->id);
            })->count();

            // Upcoming event
            $upcomingEvent = Event::where('club_id', $club->id)
                ->whereDate('event_date', '>=', now())
                ->orderBy('event_date', 'asc')
                ->first();

            $upcoming = $upcomingEvent
                ? ['remaining_days' => Carbon::now()->startOfDay()->diffInDays(Carbon::parse($upcomingEvent->event_date)->startOfDay(), false)]
                : null;

            return ClubSettingResponse::success('Settings retrieved successfully.', [
                'club' => array_merge(
                    $club->toArray(),
                    [
                        'phone' => $user->phone,
                        'address' => $user->address,
                    ]
                ),
                'settings' => $clubSetting,
                'seasons' => $club->seasons,
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
            $user = auth()->user();
            $club = $user->club;

            if (!$club) {
                return ClubSettingResponse::error('User has no associated club.', 404);
            }

            // Handle logo
            if ($logo) {
                $data['logo'] = $logo->store('club_logos', 'public');
            }

            // Handle club banner
            if ($clubBanner) {
                $data['club_banner'] = $clubBanner->store('club_banners', 'public');
            }

            // Update user's phone and address
            $user->update([
                'phone' => $data['phone'] ?? $user->phone,
                'address' => $data['address'] ?? $user->address,
            ]);

            unset($data['phone'], $data['address']);

            if (!empty($data['footer_img'])) {
                $data['footer_img'] = $data['footer_img']->store('uploads/clubs/footer', 'public');
            }

            if (!empty($data['header_img'])) {
                $data['header_img'] = $data['header_img']->store('uploads/clubs/header', 'public');
            }

            if (!empty($data['cover_image'])) {
                $data['cover_image'] = $data['cover_image']->store('uploads/clubs/cover', 'public');
            }

            // Separate Club vs ClubSetting fields
            $clubFields = [
                'club_name', 'tag_line', 'about', 'contact_details',
                'domain_type', 'domain_name',
            ];

            $clubData = array_filter($data, fn($key) => in_array($key, $clubFields), ARRAY_FILTER_USE_KEY);
            $settingData = array_diff_key($data, $clubData);

            // Update Club
            $club->update($clubData);

            // Update ClubSetting where club_id = $club->id
            $clubSetting = ClubSetting::where('club_id', $club->id)->first();
            if ($clubSetting) {
                $clubSetting->update($settingData);
            } else {
                $settingData['club_id'] = $club->id;
                $clubSetting = ClubSetting::create($settingData);
            }

            // Handle seasons
            if (!empty($data['seasons']) && is_array($data['seasons'])) {
                ClubSeason::where('club_id', $club->id)->delete();

                foreach ($data['seasons'] as $season) {
                    ClubSeason::create([
                        'club_id'    => $club->id,
                        'name'       => $season['name'] ?? null,
                        'status'     => $season['status'] ?? 'inactive',
                        'start_date' => $season['start_date'] ?? null,
                        'end_date'   => $season['end_date'] ?? null,
                    ]);
                }
            }

            return ClubSettingResponse::success('Club Settings Saved Successfully.', $clubSetting, 201);

        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600)
                ? $e->getCode()
                : 500;

            return ClubSettingResponse::error($e->getMessage(), $statusCode);
        }
    }

}
