<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Repositories\CourseRepositoryInterface;
use App\Repositories\CourseRepository;

use App\Repositories\RoleRepositoryInterface;
use App\Repositories\RoleRepository;

use App\Repositories\PermissionRepositoryInterface;
use App\Repositories\PermissionRepository;

use App\Repositories\FranchiseRepositoryInterface;
use App\Repositories\FranchiseRepository;

use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;


use App\Repositories\CourseModuleRepositoryInterface;
use App\Repositories\CourseModuleRepository;

use App\Repositories\EnrollmentRepositoryInterface;
use App\Repositories\EnrollmentRepository;

use App\Repositories\ClassScheduleRepositoryInterface;
use App\Repositories\ClassScheduleRepository;

use App\Repositories\LiveClassRepositoryInterface;
use App\Repositories\LiveClassRepository;

use App\Repositories\SignUpRepositoryInterface;
use App\Repositories\SignUpRepository;

use App\Repositories\AuthRepositoryInterface;
use App\Repositories\AuthRepository;

use App\Repositories\SchoolRepositoryInterface;
use App\Repositories\SchoolRepository;

use App\Repositories\StudentRepositoryInterface;
use App\Repositories\StudentRepository;

use App\Repositories\StudentHomeWorkRepository;
use App\Repositories\StudentHomeWorkRepositoryInterface;

use App\Repositories\StudentHomeWorkFileRepository;
use App\Repositories\StudentHomeWorkFileRepositoryInterface;

use App\Repositories\CatalogRepositoryInterface;
use App\Repositories\CatalogRepository;

use App\Repositories\EventRepositoryInterface;
use App\Repositories\EventRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
		$this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(FranchiseRepositoryInterface::class, FranchiseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
		$this->app->bind(CourseModuleRepositoryInterface::class, CourseModuleRepository::class);
        $this->app->bind(EnrollmentRepositoryInterface::class, EnrollmentRepository::class);
        $this->app->bind(ClassScheduleRepositoryInterface::class, ClassScheduleRepository::class);
        $this->app->bind(LiveClassRepositoryInterface::class, LiveClassRepository::class);
        $this->app->bind(SignUpRepositoryInterface::class, SignUpRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(StudentHomeWorkRepositoryInterface::class, StudentHomeWorkRepository::class);
        $this->app->bind(StudentHomeWorkFileRepositoryInterface::class, StudentHomeWorkFileRepository::class);

        $this->app->bind(CatalogRepositoryInterface::class, CatalogRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
