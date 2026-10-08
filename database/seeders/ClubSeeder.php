<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClubSeeder extends Seeder
{
    /**
     * Seed additional clubs, each with its own club admin and settings.
     * Safe to re-run: records are matched on admin email / club owner.
     */
    public function run(): void
    {
        $clubs = [
            [
                'username'    => 'hexhamcc',
                'first_name'  => 'Hexham',
                'club_name'   => 'Hexham Camera Club',
                'tag_line'    => 'Capturing the Tyne Valley since 1962',
                'about'       => 'A welcoming club for landscape and wildlife photographers of every level.',
                'primary'     => '#4F7CAC',
            ],
            [
                'username'    => 'blaydonps',
                'first_name'  => 'Blaydon',
                'club_name'   => 'Blaydon Photographic Society',
                'tag_line'    => 'Learning, sharing and competing together',
                'about'       => 'Weekly meetings with talks, practical nights and monthly competitions.',
                'primary'     => '#C8553D',
            ],
            [
                'username'    => 'wearsidecc',
                'first_name'  => 'Wearside',
                'club_name'   => 'Wearside Camera Club',
                'tag_line'    => 'Street, portrait and night photography',
                'about'       => 'An active club with a strong focus on print and PDI competitions.',
                'primary'     => '#2A9D8F',
            ],
            [
                'username'    => 'tynedaleps',
                'first_name'  => 'Tynedale',
                'club_name'   => 'Tynedale Photographic Society',
                'tag_line'    => 'Photography for the love of it',
                'about'       => 'Friendly society running field trips, workshops and inter-club battles.',
                'primary'     => '#8D6A9F',
            ],
            [
                'username'    => 'coastlinecc',
                'first_name'  => 'Coastline',
                'club_name'   => 'Coastline Camera Club',
                'tag_line'    => 'Seascapes, harbours and coastal light',
                'about'       => 'Based on the coast, we specialise in seascapes and long exposure work.',
                'primary'     => '#E9A23B',
            ],
        ];

        foreach ($clubs as $data) {
            $admin = User::firstOrCreate(
                ['email' => "{$data['username']}@example.com"],
                [
                    'username'   => $data['username'],
                    'first_name' => $data['first_name'],
                    'last_name'  => 'club',
                    'password'   => Hash::make('secret123'),
                    'status'     => 'approved',
                ]
            );
            $admin->assignRole('club_admin');

            $club = Club::firstOrCreate(
                ['user_id' => $admin->id],
                [
                    'club_name'       => $data['club_name'],
                    'tag_line'        => $data['tag_line'],
                    'about'           => $data['about'],
                    'contact_details' => "{$data['username']}@example.com",
                    'domain_type'     => 'subdomain',
                    'domain_name'     => "{$data['username']}.cameraclub.website",
                    'created_by'      => $admin->id,
                ]
            );

            ClubSetting::firstOrCreate(
                ['club_id' => $club->id],
                [
                    'timezone'             => 'UK',
                    'date'                 => now()->toDateString(),
                    'club_privacy'         => 'Public',
                    'theme_colors'         => 'Light',
                    'text_color'           => '#333333',
                    'primary_color'        => $data['primary'],
                    'background_color'     => '#FFFFFF',
                    'secondary_color'      => '#ECEDE6',
                    'accent_color'         => '#DD9757',
                    'typography'           => 'Inter',
                    'fonts'                => 'Inter',
                    'header_title'         => "About {$data['club_name']}",
                    'header_description'   => $data['about'],
                    'footer_text'          => 'About Our Club',
                    'footer_description'   => $data['about'],
                    'logo'                 => 'club_logos/default.png',
                    'registration'         => 'open',
                    'directory_visibility' => 'club_only',
                    'comments'             => 'enabled',
                    'likes'                => 'enabled',
                    'website_sections'     => 'Competitions',
                    'comment_preference'   => 'All',
                    'reminders'            => 'all',
                ]
            );
        }
    }
}
