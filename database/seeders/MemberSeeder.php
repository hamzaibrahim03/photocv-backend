<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'username'   => 'UpdatedJhon',
                'first_name' => 'Updated Jhon',
                'last_name'  => 'Doe',
                'about'      => 'John Doe member of the club.',
                'email'      => 'john1@doe.com',
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
                'password' => bcrypt('password'),
            ]));

            // Assign to club ID 1
            $user->clubs()->syncWithoutDetaching([
                1 => ['joined_at' => now()]
            ]);
        }
    }
}
