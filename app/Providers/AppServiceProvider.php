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

use App\Repositories\CatalogRepositoryInterface;
use App\Repositories\CatalogRepository;

use App\Repositories\EventRepositoryInterface;
use App\Repositories\EventRepository;

use App\Repositories\CompetitionRepositoryInterface;
use App\Repositories\CompetitionRepository;

use App\Repositories\NoticeRepositoryInterface;
use App\Repositories\NoticeRepository;

use App\Repositories\ClubNewsRepositoryInterface;
use App\Repositories\ClubNewsRepository;

use App\Repositories\PagesRepositoryInterface;
use App\Repositories\PagesRepository;

use App\Repositories\ClubSettingsRepositoryInterface;
use App\Repositories\ClubSettingsRepository;

use App\Repositories\MemberRepositoryInterface;
use App\Repositories\MemberRepository;

use App\Repositories\ClubDashboardRepositoryInterface;
use App\Repositories\ClubDashboardRepository;

use App\Repositories\FeatureImageRepositoryInterface;
use App\Repositories\FeatureImageRepository;

use App\Repositories\MemberAdminRepositoryInterface;
use App\Repositories\MemberAdminRepository;


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
        $this->app->bind(NoticeRepositoryInterface::class, NoticeRepository::class);
        $this->app->bind(ClubNewsRepositoryInterface::class, ClubNewsRepository::class);
        $this->app->bind(PagesRepositoryInterface::class, PagesRepository::class);
        $this->app->bind(ClubSettingsRepositoryInterface::class, ClubSettingsRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, MemberRepository::class);
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
