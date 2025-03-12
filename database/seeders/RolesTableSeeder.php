<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Insert dummy roles
        Role::create(['name' => 'Admin', 'description' => 'Administrator with full access']);
        Role::create(['name' => 'Teacher', 'description' => 'Instructor with course management rights']);
        Role::create(['name' => 'Student', 'description' => 'Student with access to courses']);
    }
}

?>