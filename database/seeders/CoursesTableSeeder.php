<?php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Franchise;
use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{
    public function run()
    {
        $franchise1 = Franchise::where('name', 'LMS Franchise 1')->first();
		$name = 'Introduction to Laravel';
		$code = fake()->name();
		$credits = fake()->numberBetween(100, 2500);
        // Insert dummy courses
        Course::create(['credits' => $credits ,'code' => $code ,'name' => $name ,'title' => 'Introduction to Laravel', 'description' => 'Learn the basics of Laravel framework', 'franchise_id' => $franchise1->id, 'price' => 50.00]);
        
		
		$name = fake()->name();		
		$credits = fake()->numberBetween(100, 2500);
		$code = fake()->name();
		Course::create(['credits' => $credits ,'code' => $code ,'name' => $name ,'title' => 'Advanced PHP Programming', 'description' => 'Master PHP with advanced techniques', 'franchise_id' => $franchise1->id, 'price' => 75.00]);
    }
}

?>