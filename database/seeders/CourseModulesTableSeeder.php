<?php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class CourseModulesTableSeeder extends Seeder
{
    public function run()
    {
        $course1 = Course::where('title', 'Introduction to Laravel')->first();
        $course2 = Course::where('title', 'Advanced PHP Programming')->first();

        // Insert dummy course modules
        CourseModule::create(['course_id' => $course1->id, 'title' => 'Module 1: Introduction', 'content' => 'Introductory content for Laravel', 'order' => 1]);
        CourseModule::create(['course_id' => $course1->id, 'title' => 'Module 2: Routing', 'content' => 'Learn Laravel routing', 'order' => 2]);
        CourseModule::create(['course_id' => $course2->id, 'title' => 'Module 1: PHP Basics', 'content' => 'Understanding PHP basics', 'order' => 1]);
        CourseModule::create(['course_id' => $course2->id, 'title' => 'Module 2: Advanced Techniques', 'content' => 'Learning advanced PHP concepts', 'order' => 2]);
    }
}

?>