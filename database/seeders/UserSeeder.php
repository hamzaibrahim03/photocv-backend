<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@photocv.com'],
            [
                'username' => 'superadmin',
                'password'   => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Create a Club Admin
        $clubadmin = User::firstOrCreate(
            ['email' => 'clubadmin@photocv.com'],
            [
                'username' => 'clubadmin',
                'password'   => Hash::make('password'),
            ]
        );
        $clubadmin->assignRole('club_admin');

        // Create a Member User
        $member = User::firstOrCreate(
            ['email' => 'member@photocv.com'],
            [
                'username' => 'member',
                'password'   => Hash::make('password'),
            ]
        );
        $member->assignRole('member');
    }
}
