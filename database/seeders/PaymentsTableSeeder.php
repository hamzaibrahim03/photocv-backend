<?php
namespace Database\Seeders;

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class PaymentsTableSeeder extends Seeder
{
    public function run()
    {
        $student = User::where('id', '1')->first();
        $course1 = Course::where('title', 'Introduction to Laravel')->first();

        // Insert dummy payment
        Payment::create(['user_id' => $student->id, 'course_id' => $course1->id, 'amount' => 50.00, 'payment_method' => 'credit_card', 'payment_status' => 'successful']);
    }
}

?>