<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\{User, Gallery, Photo};

class PhotoSeeder extends Seeder
{
    public function run(): void
    {
        $basePath = database_path('seeders/data/ryton-club-data/galleries');

        // Fetch galleries created by club admin
        $galleries = Gallery::where('club_id', 1)->get();

        foreach ($galleries as $gallery) {
            $galleryFolder = $basePath . '/' . $gallery->gallery_name;

            if (is_dir($galleryFolder)) {
                $files = glob($galleryFolder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

                foreach ($files as $file) {
                    // Save file to storage/public/member-galleries
                    $targetPath = 'member-galleries/' . $gallery->id . '/' . basename($file);
                    Storage::disk('public')->put($targetPath, file_get_contents($file));

                    // Assign uploader randomly from members
                    $uploader = User::role('member')->inRandomOrder()->first();

                    Photo::create([
                        'gallery_id'   => $gallery->id,
                        'title'        => pathinfo($file, PATHINFO_FILENAME),
                        'image'        => $targetPath, // stored path
                        'description'  => 'Uploaded sample photo for ' . $gallery->gallery_name,
                        'is_active'    => true,
                        'allow_cc'     => false,
                        'uploaded_by'  => $uploader->id ?? 1,
                    ]);
                }
            }
        }
    }
}
