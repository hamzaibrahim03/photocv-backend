<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            ['gallery_name' => 'Moss Sporophytes'],
            ['gallery_name' => 'Odd Tranquillity'],
            ['gallery_name' => 'Backlit Poppy'],
            ['gallery_name' => 'Orange & Rocker'],
            ['gallery_name' => 'Daddy’s Boy'],
            ['gallery_name' => 'Music Love Dance'],
            ['gallery_name' => 'Scaleber Force'],
            ['gallery_name' => 'Early mist clearing'],
            ['gallery_name' => 'Female chaffinch'],
            ['gallery_name' => 'Queen Victorias View'],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create([
                'member_id'    => 2,
                'club_id'      => 1,
                'type'         => 'club',
                'gallery_name' => $gallery['gallery_name'],
                'is_active'    => true,
            ]);
        }

        // Member galleries (one per approved member)
        $members = User::role('member')->where('status', 'approved')->get();

        foreach ($members as $member) {
            Gallery::create([
                'member_id'    => $member->id,
                'club_id'      => 1,
                'type'         => 'member',
                'gallery_name' => $member->first_name . "'s Gallery",
                'is_active'    => true,
            ]);
        }
    }
}
