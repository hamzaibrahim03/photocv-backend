<?php
namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\LiveClass;
use Illuminate\Database\Seeder;

class LiveClassesTableSeeder extends Seeder
{
    public function run()
    {
        $classSchedule = ClassSchedule::first();

        // Insert dummy live class
        LiveClass::create(['class_schedule_id' => $classSchedule->id, 'meeting_url' => 'https://example.com/meeting', 'status' => 'scheduled']);
    }
}

?>