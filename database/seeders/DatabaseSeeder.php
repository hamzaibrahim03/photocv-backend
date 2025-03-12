<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
		
		$this->call(FranchisesTableSeeder::class);		
		$this->call(CoursesTableSeeder::class);
		$this->call(CertificationsTableSeeder::class);		
		$this->call(ClassSchedulesTableSeeder::class);
		
		$this->call(CourseModulesTableSeeder::class);
		$this->call(EnrollmentsTableSeeder::class);
		$this->call(LiveClassesTableSeeder::class);
		$this->call(PaymentsTableSeeder::class);
		$this->call(PermissionsTableSeeder::class);
		$this->call(RolesTableSeeder::class);
		$this->call(UserPermissionsTableSeeder::class);	
		
		
    }
}
