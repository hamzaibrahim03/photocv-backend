<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'username'   => 'jhondoe',
                'first_name' => 'Jhon',
                'last_name'  => 'Doe',
                'about'      => 'John Doe member of the club.',
                'email'      => 'john@doe.com',
                'address'    => 'Address of John Doe',
                'postcode'   => '5544',
                'country'    => 'USA',
                'phone'      => '+98754',
                'bio'        => 'Bio of the John Doe profile',
                'status'     => 'approved',
            ],
            [
                'username'   => 'janesmith',
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
                'about'      => 'Jane Smith joined recently.',
                'email'      => 'jane@smith.com',
                'address'    => 'Address of Jane Smith',
                'postcode'   => '10001',
                'country'    => 'USA',
                'phone'      => '+12345',
                'bio'        => 'Photographer and nature enthusiast.',
                'status'     => 'approved',
            ],
            [
                'username'   => 'mikebrown',
                'first_name' => 'Mike',
                'last_name'  => 'Brown',
                'about'      => 'Mike is part of the camera club.',
                'email'      => 'mike@brown.com',
                'address'    => 'Address of Mike Brown',
                'postcode'   => '60601',
                'country'    => 'USA',
                'phone'      => '+54321',
                'bio'        => 'Loves portrait photography.',
                'status'     => 'approved',
            ],
        ];

        foreach ($members as $data) {
            $user = User::create(array_merge($data, [
                'password' => Hash::make('secret123'),
            ]));

            // Assign to club ID 1
            $user->clubs()->syncWithoutDetaching([
                1 => ['joined_at' => now()]
            ]);

            $user->assignRole('member');
        }

        //judges
        $judges = [
            [
                'username'   => 'kimhonda',
                'title' => 'Photographer',
                'first_name' => 'Kim',
                'last_name' => 'Honda',
                'about' => 'Kim member of the club.',
                'email' => 'kim@honda.com',
                'phone' => '+98754',
                'socials' => [
                    ['social_media_name' => 'facebook', 'social_link' => 'http://facebook.com'],
                    ['social_media_name' => 'instagram', 'social_link' => 'http://instagram.com'],
                ]
            ],
            [
                'username'   => 'johnsmith',
                'title' => 'Photographer',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'about' => 'Experienced judge for color photography.',
                'email' => 'john@judge.com',
                'phone' => '+12345',
                'socials' => []
            ],
            [
                'username'   => 'sarahlee',
                'title' => 'Photographer',
                'first_name' => 'Sarah',
                'last_name' => 'Lee',
                'about' => 'Judge with expertise in sports photography.',
                'email' => 'sarah@judge.com',
                'phone' => '+22345',
                'socials' => []
            ],
            [
                'username'   => 'micheal',
                'title' => 'Photographer',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'about' => 'Judge specializing in wildlife.',
                'email' => 'michael@judge.com',
                'phone' => '+32345',
                'socials' => []
            ],
        ];

        foreach ($judges as $data) {
            $socials = $data['socials'] ?? [];
            unset($data['socials']);

            $user = User::create(array_merge($data, [
                'password' => Hash::make('secret123'),
            ]));

            // $user = User::create($data);

            // assign to club
            $user->clubs()->syncWithoutDetaching([
                1 => ['joined_at' => now()]
            ]);

            // assign role if using Spatie
            $user->assignRole('Internal Comp Secretary');

            // add socials
            foreach ($socials as $social) {
                \App\Models\MemberSocialLink::create([
                    'member_id' => $user->id,
                    'social_media_name' => $social['social_media_name'],
                    'social_link' => $social['social_link'],
                ]);
            }
        }
    }
    
}
