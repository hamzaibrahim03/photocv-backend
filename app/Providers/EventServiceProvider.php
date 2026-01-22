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
use App\Events\UserRegistered;
use App\Listeners\SendUserRegisteredEmail;

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

        UserRegistered::class => [
            SendUserRegisteredEmail::class,
        ],

        \App\Events\ContentInteracted::class => [
            \App\Listeners\CreateInteractionNotification::class,
        ],
        \App\Events\CompetitionEntryAdded::class => [
            \App\Listeners\CreateCompetitionEntryNotification::class,
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
