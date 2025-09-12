<?php

namespace App\Repositories\ClubPublic;

use App\Repositories\ClubAdmin\ClubDashboardRepository;
use App\Repositories\ClubAdmin\EventRepository;
use App\Repositories\ClubAdmin\CompetitionRepository;
use App\Repositories\ClubAdmin\ClubNewsRepository;
use App\Repositories\ClubAdmin\ClubSettingsRepository;
use App\Repositories\ClubAdmin\ClubGalleryRepository;
use App\Repositories\ClubAdmin\CompetitionResultRepositoryInterface;
use App\Repositories\NoticeRepositoryInterface;
use App\Repositories\MemberRepositoryInterface;
use App\Repositories\ClubAdmin\PagesRepository;
use App\Repositories\UserRepository;

class ClubRepositories
{
    public function __construct(
        public ClubDashboardRepository $clubDashboardRepo,
        public EventRepository $eventRepo,
        public CompetitionRepository $competitionRepo,
        public ClubNewsRepository $clubNewsRepo,
        public ClubSettingsRepository $clubSettingRepo,
        public ClubGalleryRepository $clubGalleryRepo,
        public CompetitionResultRepositoryInterface $competitionResultRepository,
        public NoticeRepositoryInterface $noticeRepository,
        public MemberRepositoryInterface $memberRepository,
        public PagesRepository $pageRepo,
        public UserRepository $userRepo,
    ) {}
}
