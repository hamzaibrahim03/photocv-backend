<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
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

        // Create another Club Admin with a club
        $john = User::firstOrCreate(
            ['email' => 'johndoe@photocv.com'],
            [
                'username' => 'johnphotocv',
                'password' => Hash::make('secret123'),
            ]
        );
        $john->assignRole('club_admin');

        // Create Club for John if not already exists
        $club = Club::firstOrCreate(
            ['user_id' => $john->id], // assuming 'user_id' is the foreign key in clubs table
            [
                'club_name'           => 'My First Club',
                'tag_line'            => 'Anyone can join',
                'domain_type'         => 'custom',
                'registration'        => 'open',
                'directory_visibility'=> 'visible',
                'comments'            => 'enabled',
                'likes'               => 'enabled',
                'news'                => 'enabled',
                'events'              => 'enabled',
                'galleries'           => 'enabled',
                'competitions'        => 'enabled',
                'reminders'           => 'all',
                'home_page_blocks'    => 'all',
            ]
        );
    }
}
