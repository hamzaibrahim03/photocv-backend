<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            ['gallery_name' => 'Nature'],
            ['gallery_name' => 'Indoors'],
            ['gallery_name' => 'Seascapes'],
            ['gallery_name' => 'Street'],
            ['gallery_name' => 'Portrait'],
            ['gallery_name' => 'Event'],
            ['gallery_name' => 'Weather'],
            ['gallery_name' => 'Architecture'],
            ['gallery_name' => 'Mono'],
            ['gallery_name' => 'Sunset'],
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
    }
}
