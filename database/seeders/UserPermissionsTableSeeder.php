<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserPermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('id', '1')->first();
        $createCoursePermission = Permission::where('name', 'create_course')->first();

        // Insert dummy user permissions
        $admin->permissions()->attach($createCoursePermission);
    }
}

?>