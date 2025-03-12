<?php
namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        // Insert dummy permissions
        Permission::create(['name' => 'create_course', 'description' => 'Create new courses']);
        Permission::create(['name' => 'update_course', 'description' => 'Update existing courses']);
        Permission::create(['name' => 'delete_course', 'description' => 'Delete courses']);
        Permission::create(['name' => 'view_course', 'description' => 'View courses']);
        Permission::create(['name' => 'enroll_course', 'description' => 'Enroll in a course']);
    }
}

?>