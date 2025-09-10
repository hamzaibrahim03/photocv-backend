<?php

namespace App\Repositories\ClubAdmin;

use App\Models\ClubSetting;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\ClubSeason;
use App\Http\Responses\ClubSettingResponse;
use App\Models\ClubCoverImage;
use App\Models\Competition;

class ClubSettingsRepository implements ClubSettingsRepositoryInterface
{
    public function getAllClubSettings($userId = null)
    {
        try {
            $user = User::findOrFail($userId ?? auth()->id());
            $club = $user->club;

            if (!$club) {
                return ClubSettingResponse::error('No club found for the current user.', 404);
            }

            $club->load('setting.coverImages');
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

            // Total counts (all seasons)
            $totalEventsCount = Event::where('club_id', $club->id)->count();
            $totalCompetitionsCount = Competition::where('club_id', $club->id)->count();

            // Counts grouped by season (using date ranges)
            $seasonsWithCounts = $club->seasons->map(function ($season) use ($club) {
                $eventsCount = Event::where('club_id', $club->id)
                    ->whereBetween('event_date', [$season->start_date, $season->end_date])
                    ->count();

                $competitionsCount = Competition::where('club_id', $club->id)
                    ->whereBetween('start_date', [$season->start_date, $season->end_date])
                    ->count();

                    return [
                    'id' => $season->id,
                    'name' => $season->name,
                    'status' => $season->status,
                    'start_date' => $season->start_date,
                    'end_date' => $season->end_date,
                    'events_count' => $eventsCount,
                    'competitions_count' => $competitionsCount,
                ];
            });

            return ClubSettingResponse::success('Settings retrieved successfully.', [
                'club' => array_merge(
                    collect($club)->except(['setting'])->toArray(),
                    [
                        'phone' => $user->phone,
                        'address' => $user->address,
                    ]
                ),
                'settings' => $clubSetting,
                'seasons' => $seasonsWithCounts,
                'total_members' => $totalMemberCount,
                'total_events' => $totalEventsCount,
                'total_competitions' => $totalCompetitionsCount,
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

            if (!empty($data['cover_images']) && is_array($data['cover_images'])) {
                // If replacing, delete old ones
                ClubCoverImage::where('club_setting_id', $clubSetting->id)->delete();

                foreach ($data['cover_images'] as $image) {
                    $path = $image->store('uploads/clubs/cover', 'public');
                    ClubCoverImage::create([
                        'club_setting_id' => $clubSetting->id,
                        'image_path'      => $path,
                    ]);
                }
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
