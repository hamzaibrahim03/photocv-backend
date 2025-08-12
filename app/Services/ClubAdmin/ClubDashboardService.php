<?php

namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\ClubDashboardRepositoryInterface;
use App\Repositories\ClubAdmin\ClubGalleryRepository;

class ClubDashboardService
{
    protected $clubDashboardRepository;
    protected ClubGalleryRepository $clubGalleryRepo;

    public function __construct(ClubDashboardRepositoryInterface $clubDashboardRepository, ClubGalleryRepository $clubGalleryRepo,)
    {
        $this->clubDashboardRepository = $clubDashboardRepository;
        $this->clubGalleryRepo = $clubGalleryRepo;
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
            'clubGalleries' => $this->clubGalleryRepo->getClubGalleries(auth()->user()->id),
            'total_members_count' => $latestMembersData['total_count'],
            'current_month_activities' => $this->clubDashboardRepository->getCurrentMonthActivities($clubId),
            'recent_results' => $this->clubDashboardRepository->getRecentResults($clubId),
        ];
    }
}