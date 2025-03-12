<?php
namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassSchedulesTableSeeder extends Seeder
{
    public function run()
    {
        $course1 = Course::where('title', 'Introduction to Laravel')->first();
        $teacher = User::where('id', '1')->first();

        // Insert dummy class schedules
        ClassSchedule::create(['course_id' => $course1->id, 'title' => 'Laravel Basics', 'start_time' => now(), 'end_time' => now()->addHour(), 'teacher_id' => $teacher->id]);
    }
}

?>