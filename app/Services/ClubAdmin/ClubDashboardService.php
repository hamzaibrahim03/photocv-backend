<?php

namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\ClubDashboardRepositoryInterface;
use App\Repositories\ClubAdmin\CompetitionResultRepositoryInterface;
use App\Repositories\ClubAdmin\ClubGalleryRepository;
use App\Repositories\ClubAdmin\CompetitionRepositoryInterface;
use App\Repositories\ClubAdmin\PagesRepositoryInterface;

class ClubDashboardService
{
    protected $clubDashboardRepository;
    protected $competitionResultRepository;
    protected $competitionRepository;
    protected $pagesRepository;
    protected ClubGalleryRepository $clubGalleryRepo;

    public function __construct(
        ClubDashboardRepositoryInterface $clubDashboardRepository,
        ClubGalleryRepository $clubGalleryRepo,
        CompetitionResultRepositoryInterface $competitionResultRepository,
        CompetitionRepositoryInterface $competitionRepository,
        PagesRepositoryInterface $pagesRepository
    ){
        $this->clubDashboardRepository = $clubDashboardRepository;
        $this->clubGalleryRepo = $clubGalleryRepo;
        $this->competitionResultRepository = $competitionResultRepository;
        $this->competitionRepository = $competitionRepository;
        $this->pagesRepository = $pagesRepository;
    }

    public function getDashboardData(int $clubId)
    {
        $latestMembersData = $this->clubDashboardRepository->getLatestMembers($clubId);
        $latestEventsData = $this->clubDashboardRepository->getLatestEvents($clubId);

        return [
            'user_details' => $this->clubDashboardRepository->getAuthUserDetails(),
            'events' => $latestEventsData['events'],
            'upcoming_event' => $latestEventsData['upcoming_event'],
            'competitions' => $this->competitionRepository->getLatestCompetitionsByLimit($clubId),
            'pages' => $this->pagesRepository->getLatestPagesByLimit($clubId),
            'member_notices' => $this->clubDashboardRepository->getLatestNotices($clubId),
            'club_news' => $this->clubDashboardRepository->getLatestClubNews($clubId),
            'latest_members' => $latestMembersData['members'],
            'member_galleries' => $this->clubDashboardRepository->getMembersGallerries($clubId),
            'clubGalleries' => $this->clubGalleryRepo->getClubGalleries(auth()->user()->id),
            'total_members_count' => $latestMembersData['total_count'],
            'current_month_activities' => $this->clubDashboardRepository->getCurrentMonthActivities($clubId),
            // 'recent_results' => $this->clubDashboardRepository->getRecentResults($clubId),
            'recent_results' => $this->competitionResultRepository->getAllPublishedResults(null, true),
        ];
    }
}