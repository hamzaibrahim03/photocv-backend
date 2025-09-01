<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Repositories\RoleRepositoryInterface;
use App\Repositories\RoleRepository;

use App\Repositories\PermissionRepositoryInterface;
use App\Repositories\PermissionRepository;

use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;

use App\Repositories\SignUpRepositoryInterface;
use App\Repositories\SignUpRepository;

use App\Repositories\AuthRepositoryInterface;
use App\Repositories\AuthRepository;

use App\Repositories\ClubAdmin\CatalogRepositoryInterface;
use App\Repositories\ClubAdmin\CatalogRepository;

use App\Repositories\ClubAdmin\EventRepositoryInterface;
use App\Repositories\ClubAdmin\EventRepository;

use App\Repositories\ClubAdmin\CompetitionRepositoryInterface;
use App\Repositories\ClubAdmin\CompetitionRepository;

use App\Repositories\ClubAdmin\CompetitionResultRepositoryInterface;
use App\Repositories\ClubAdmin\CompetitionResultRepository;

use App\Repositories\Member\MemberCompetitionRepositoryInterface;
use App\Repositories\Member\MemberCompetitionRepository;

use App\Repositories\NoticeRepositoryInterface;
use App\Repositories\NoticeRepository;

use App\Repositories\ClubAdmin\ClubNewsRepositoryInterface;
use App\Repositories\ClubAdmin\ClubNewsRepository;

use App\Repositories\ClubAdmin\PagesRepositoryInterface;
use App\Repositories\ClubAdmin\PagesRepository;

use App\Repositories\ClubAdmin\ClubSettingsRepositoryInterface;
use App\Repositories\ClubAdmin\ClubSettingsRepository;

use App\Repositories\MemberRepositoryInterface;
use App\Repositories\MemberRepository;

use App\Repositories\ClubAdmin\MemberRequestRepositoryInterface;
use App\Repositories\ClubAdmin\MemberRequestRepository;

use App\Repositories\Member\MemberNoteRepositoryInterface;
use App\Repositories\Member\MemberNoteRepository;

use App\Repositories\Member\MemberClassLogRepositoryInterface;
use App\Repositories\Member\MemberClassLogRepository;

use App\Repositories\ClubAdmin\ClubDashboardRepositoryInterface;
use App\Repositories\ClubAdmin\ClubDashboardRepository;

use App\Repositories\ClubAdmin\FeatureImageRepositoryInterface;
use App\Repositories\ClubAdmin\FeatureImageRepository;

use App\Repositories\Member\MemberAdminRepositoryInterface;
use App\Repositories\Member\MemberAdminRepository;

use App\Repositories\Member\MemberInterestBrandRepositoryInterface;
use App\Repositories\Member\MemberInterestBrandRepository;

use App\Repositories\Member\MemberPracticeLogRepositoryInterface;
use App\Repositories\Member\MemberPracticeLogRepository;

use App\Repositories\ClubAdmin\ClubGalleryRepositoryInterface;
use App\Repositories\ClubAdmin\ClubGalleryRepository;

use App\Repositories\ClubAdmin\CompetitionGlobalSettingRepositoryInterface;
use App\Repositories\ClubAdmin\CompetitionGlobalSettingRepository;

use App\Repositories\Judge\CompetitionEntryScoreRepository;
use App\Repositories\Judge\CompetitionEntryScoreRepositoryInterface;

use App\Repositories\Judge\JudgeRepository;
use App\Repositories\Judge\JudgeRepositoryInterface;

use App\Repositories\Member\BookingRepository;
use App\Repositories\Member\BookingRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
		$this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SignUpRepositoryInterface::class, SignUpRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);

        $this->app->bind(CatalogRepositoryInterface::class, CatalogRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);

        $this->app->bind(CompetitionRepositoryInterface::class, CompetitionRepository::class);
        $this->app->bind(CompetitionGlobalSettingRepositoryInterface::class, CompetitionGlobalSettingRepository::class);
        $this->app->bind(MemberCompetitionRepositoryInterface::class, MemberCompetitionRepository::class);
        $this->app->bind(CompetitionResultRepositoryInterface::class, CompetitionResultRepository::class);
        $this->app->bind(CompetitionEntryScoreRepositoryInterface::class, CompetitionEntryScoreRepository::class);
        $this->app->bind(JudgeRepositoryInterface::class, JudgeRepository::class);

        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);

        $this->app->bind(NoticeRepositoryInterface::class, NoticeRepository::class);
        $this->app->bind(ClubGalleryRepositoryInterface::class, ClubGalleryRepository::class);
        $this->app->bind(ClubNewsRepositoryInterface::class, ClubNewsRepository::class);
        $this->app->bind(PagesRepositoryInterface::class, PagesRepository::class);
        $this->app->bind(ClubSettingsRepositoryInterface::class, ClubSettingsRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, MemberRepository::class);
        $this->app->bind(MemberRequestRepositoryInterface::class, MemberRequestRepository::class);
        $this->app->bind(MemberNoteRepositoryInterface::class, MemberNoteRepository::class);
        $this->app->bind(MemberPracticeLogRepositoryInterface::class, MemberPracticeLogRepository::class);
        $this->app->bind(MemberInterestBrandRepositoryInterface::class, MemberInterestBrandRepository::class);
        $this->app->bind(MemberClassLogRepositoryInterface::class, MemberClassLogRepository::class);
        $this->app->bind(ClubDashboardRepositoryInterface::class, ClubDashboardRepository::class);
        $this->app->bind(FeatureImageRepositoryInterface::class, FeatureImageRepository::class);
        $this->app->bind(MemberAdminRepositoryInterface::class, MemberAdminRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
