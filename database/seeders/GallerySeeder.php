<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            ['gallery_name' => 'Nature Photography'],
            ['gallery_name' => 'Portrait Shots'],
            ['gallery_name' => 'Black & White Collection'],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create([
                'member_id'    => 4,
                'club_id'      => 1,
                'type'         => 'club',
                'gallery_name' => $gallery['gallery_name'],
                'is_active'    => true,
            ]);
        }
    }
}
