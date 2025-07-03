<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\MemberNotice;
use App\Models\Page;
use App\Models\Competition;
use App\Models\ClubNews;
use App\Models\User;
use Carbon\Carbon;

class ClubDashboardRepository implements ClubDashboardRepositoryInterface
{
    public function getAuthUserDetails()
    {
        $user = auth()->user();

        return [
            'username'      => $user->username,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'role'          => $user->getRoleNames()->first(),
        ];

    }

    public function getLatestEvents(int $clubId, int $limit = 3)
    {
        $events = Event::where('club_id', $clubId)
            ->orderBy('event_date', 'desc')
            ->limit($limit)
            ->get();

        $upcoming = Event::where('club_id', $clubId)
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($event) {
                $remainingDays = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($event->event_date)->startOfDay(), false);
                return ['remaining_days' => $remainingDays];
            });


        return [
            'events' => $events,
            'upcoming_event' => $upcoming->isNotEmpty() ? $upcoming->first() : null,
        ];
    }

    public function getLatestCompetitions(int $clubId, int $limit = 3)
    {
        return Competition::where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLatestPages(int $clubId, int $limit = 6)
    {
        return Page::where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLatestNotices(int $clubId, int $limit = 3)
    {
        return MemberNotice::where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLatestClubNews(int $clubId, int $limit = 3)
    {
        return ClubNews::where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Method to return latest members
     * @param int $clubId
     * @param int $limit
     * @return array{members: \Illuminate\Database\Eloquent\Collection<int, User>, total_count: int}
     */
    public function getLatestMembers(int $clubId, int $limit = 3)
    {
        $members = User::whereHas('clubs', function ($query) use ($clubId) {
            $query->where('club_id', $clubId);
        })
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();

        $totalCount = User::whereHas('clubs', function ($query) use ($clubId) {
                $query->where('club_id', $clubId);
            })
            ->count();

        return [
            'members' => $members,
            'total_count' => $totalCount,
        ];
    }

    /**
     * Method to return all members galleries
     * @param int $clubId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getMembersGallerries(int $clubId, int $limit = 10)
    {
        return User::whereHas('clubs', function ($query) use ($clubId) {
                $query->where('club_id', $clubId);
            })
            ->with([
                'galleries' => function ($galleryQuery) {
                    $galleryQuery->where('is_active', true)
                        ->with(['photos' => function ($photoQuery) {
                            $photoQuery->where('is_active', true)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(1);
                        }]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getCurrentMonthActivities(int $clubId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Events of current month
        $events = Event::where('club_id', $clubId)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name']) // Only get these fields
            ->map(function ($event) {
                return [
                    'date' => $event->event_date->toDateString(),
                    'name' => $event->name,
                ];
            });

        // Competitions of current month
        $competitions = Competition::where('club_id', $clubId)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_date', 'asc')
            ->get(['start_date', 'name']) // Only get these fields
            ->map(function ($competition) {
                return [
                    'date' => $competition->start_date->toDateString(),
                    'name' => $competition->name,
                ];
            });

        return [
            'events' => $events,
            'competitions' => $competitions,
        ];
    }

}