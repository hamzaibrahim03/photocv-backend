<?php
namespace Database\Seeders;

use App\Models\Certification;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CertificationsTableSeeder extends Seeder
{
    public function run()
    {
        $student = User::where('id', '1')->first();
        $course1 = Course::where('title', 'Introduction to Laravel')->first();

        // Insert dummy certification
        Certification::create(['course_id' => $course1->id, 'user_id' => $student->id, 'certificate_code' => 'CERT12345']);
    }
}

?>