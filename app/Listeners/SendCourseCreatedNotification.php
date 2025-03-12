<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCourseCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\CourseCreated  $event
     * @return void
     */
    public function handle(CourseCreated $event)
    {
        // Access the course instance via $event->course
        $course = $event->course;

        // Here, we log the course creation (as an example)
        Log::info('A new course has been created: ' . $course->name);

        // You could also send a notification or perform other actions here
        // For example:
        // Mail::to('admin@example.com')->send(new CourseCreatedMail($course));
    }
}

