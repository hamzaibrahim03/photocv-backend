<?php

namespace App\Services\Judge;

use App\Repositories\UserRepositoryInterface;
use App\Repositories\Judge\JudgeRepositoryInterface;
use App\Repositories\ClubAdmin\EventRepositoryInterface;

class JudgeService
{
    protected $userRepository;
    protected $judgeRepository;
    protected $eventRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        JudgeRepositoryInterface $judgeRepository,
        EventRepositoryInterface $eventRepository
    ){
        $this->userRepository = $userRepository;
        $this->judgeRepository = $judgeRepository;
        $this->eventRepository = $eventRepository;
    }

    public function getDashboardData($judgeId, $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'judge_information'     => $this->userRepository->getById($judgeId),
                'posting_information'   => $this->judgeRepository->getJudgePostingInformation($judgeId),
                'upcoming_events'       => $this->eventRepository->getUpcomingEventsForAllClubs($judgeId),
                'upcoming_competitions' => $this->judgeRepository->getUpcomingCompetitionsForJudge($judgeId),
                'clubs'                 => $this->judgeRepository->getClubsWithUpcomingCompetitions($judgeId, $request),
                'recent_judging'        => $this->judgeRepository->getAllRecentJudgingByUser($judgeId),
            ],
        ], 200);
    }

    public function getCompetitionsForJudge($request, $judgeId, $clubId)
    {
        return $this->judgeRepository->getCompetitionsForJudgeInClub($request, $judgeId, $clubId);
    }

}
