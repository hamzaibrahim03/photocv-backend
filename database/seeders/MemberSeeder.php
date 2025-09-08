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
                'username'   => 'kamranchohdry',
                'first_name' => 'Kamran',
                'last_name'  => 'Chohdry',
                'about'      => '',
                'email'      => 'kamran@photocv.com',
                'address'    => '',
                'postcode'   => '',
                'country'    => 'UK',
                'phone'      => '',
                'bio'        => '',
                'status'     => 'approved',
            ],
            [
                'username'   => 'fionahaughton',
                'first_name' => 'Fiona',
                'last_name'  => 'Haughton',
                'about'      => '',
                'email'      => 'fiona@photocv.com',
                'address'    => '',
                'postcode'   => '',
                'country'    => 'UK',
                'phone'      => '',
                'bio'        => 'Photographer and nature enthusiast.',
                'status'     => 'approved',
            ],
            [
                'username'   => 'keitharcher',
                'first_name' => 'Keith',
                'last_name'  => 'Archer',
                'about'      => '',
                'email'      => 'keith@photocv.com',
                'address'    => '',
                'postcode'   => '',
                'country'    => 'UK',
                'phone'      => '',
                'bio'        => 'Loves portrait photography.',
                'status'     => 'approved',
            ],
            [
                'username'   => 'alanjudd',
                'first_name' => 'Alan',
                'last_name'  => 'Judd',
                'about'      => '',
                'email'      => 'alan@photocv.com',
                'address'    => '',
                'postcode'   => '',
                'country'    => 'UK',
                'phone'      => '',
                'bio'        => 'Loves portrait photography.',
                'status'     => 'approved',
            ],
            [
                'username'   => 'lindasnaith',
                'first_name' => 'Linda',
                'last_name'  => 'Snaith',
                'about'      => '',
                'email'      => 'linda@photocv.com',
                'address'    => '',
                'postcode'   => '',
                'country'    => 'UK',
                'phone'      => '',
                'bio'        => 'Loves portrait photography.',
                'status'     => 'approved',
            ],
            [
                'username'   => 'gordoncarlton',
                'first_name' => 'Gordon',
                'last_name'  => 'Carlton',
                'about'      => '',
                'email'      => 'gordon@photocv.com',
                'address'    => '',
                'postcode'   => '',
                'country'    => 'UK',
                'phone'      => '',
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
                1 => ['joined_at' => now(), 'status' => 'approved']
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
            ]
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
