<?php
namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class EnrollmentsTableSeeder extends Seeder
{
    public function run()
    {
        $student = User::where('id', '1')->first();
        $course1 = Course::where('title', 'Introduction to Laravel')->first();

        // Insert dummy enrollments
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course1->id, 'status' => 'enrolled', 'payment_status' => 'paid']);
    }
}

?>