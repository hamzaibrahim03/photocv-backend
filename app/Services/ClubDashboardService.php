<?php

namespace App\Services;

use App\Repositories\ClubDashboardRepositoryInterface;

class ClubDashboardService
{
    protected $clubDashboardRepository;

    public function __construct(ClubDashboardRepositoryInterface $clubDashboardRepository)
    {
        $this->clubDashboardRepository = $clubDashboardRepository;
    }

    public function getDashboardData(int $clubId)
    {
        $latestMembersData = $this->clubDashboardRepository->getLatestMembers($clubId);
        $latestEventsData = $this->clubDashboardRepository->getLatestEvents($clubId);

        return [
            'user_details' => $this->clubDashboardRepository->getAuthUserDetails(),
            'events' => $latestEventsData['events'],
            'upcoming_event' => $latestEventsData['upcoming_event'],
            'competitions' => $this->clubDashboardRepository->getLatestCompetitions($clubId),
            'pages' => $this->clubDashboardRepository->getLatestPages($clubId),
            'member_notices' => $this->clubDashboardRepository->getLatestNotices($clubId),
            'club_news' => $this->clubDashboardRepository->getLatestClubNews($clubId),
            'latest_members' => $latestMembersData['members'],
            'member_galleries' => $this->clubDashboardRepository->getMembersGallerries($clubId),
            'club_galleries' => $this->clubDashboardRepository->getMembersGallerries($clubId),
            'total_members_count' => $latestMembersData['total_count'],
            'current_month_activities' => $this->clubDashboardRepository->getCurrentMonthActivities($clubId),
            'recent_results' => $this->clubDashboardRepository->getRecentResults($clubId),
        ];
    }
}