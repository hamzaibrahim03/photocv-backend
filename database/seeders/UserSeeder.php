<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubSetting;
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
            ['user_id' => $john->id],
            [
                'club_name'    => 'My First Club',
                'tag_line'     => 'Anyone can join',
                'domain_type'  => 'custom',
                'domain_name'  => 'myfirstclub.com',
                'created_by'   => $john->id,
            ]
        );

        // Create Club Settings for John's club
        ClubSetting::firstOrCreate(
            ['club_id' => $club->id],
            [
                'registration'         => 'open',
                'directory_visibility' => 'visible',
                'comments'             => 'enabled',
                'likes'                => 'enabled',
                'website_sections'     => 'News', // Use one of the enum values, or customize logic
                'reminders'            => 'all',
                'header_title'         => 'About title',
                'header_description'   => 'About Description',
                'footer_text'          => 'Footer Description',
            ]
        );
    }
}
