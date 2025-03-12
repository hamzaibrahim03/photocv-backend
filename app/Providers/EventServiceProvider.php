<?php
namespace App\Providers;

use App\Events\CourseCreated;
use App\Listeners\SendCourseCreatedNotification;
use App\Events\CourseModuleCreated;
use App\Listeners\SendCourseModuleCreatedNotification;
use App\Events\EnrollmentCreated;
use App\Listeners\SendEnrollmentCreatedNotification;
use App\Events\ClassScheduleCreated;
use App\Listeners\SendClassScheduleCreatedNotification;
use App\Events\LiveClassCreated;
use App\Listeners\SendLiveClassCreatedNotification;
use App\Events\SchoolCreated;
use App\Listeners\LogSchoolCreation;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CourseCreated::class => [
            SendCourseCreatedNotification::class,
        ],
		CourseModuleCreated::class => [
            SendCourseModuleCreatedNotification::class,
        ],
        EnrollmentCreated::class => [
            SendEnrollmentCreatedNotification::class,
        ],
        ClassScheduleCreated::class => [
            SendClassScheduleCreatedNotification::class,
        ],
        LiveClassCreated::class => [
            SendLiveClassCreatedNotification::class,
        ],

        SchoolCreated::class => [
            LogSchoolCreation::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
