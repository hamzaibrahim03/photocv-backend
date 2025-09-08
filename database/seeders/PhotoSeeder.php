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
        $baseClubPath   = database_path('seeders/data/ryton-club-data/galleries');
        $baseMemberPath = database_path('seeders/data/ryton-club-data/member-galleries');

        $galleries = Gallery::with('member')->where('club_id', 1)->get();

        foreach ($galleries as $gallery) {
            $galleryFolder = null;

            if ($gallery->type === 'club') {
                $galleryFolder = $baseClubPath . '/' . $gallery->gallery_name;
            } elseif ($gallery->type === 'member' && $gallery->member) {
                $galleryFolder = $baseMemberPath . '/' . $gallery->member->username;
            }

            if ($galleryFolder && is_dir($galleryFolder)) {
                $files = glob($galleryFolder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

                foreach ($files as $file) {
                    $targetPath = "member-galleries/{$gallery->id}/" . basename($file);
                    Storage::disk('public')->put($targetPath, file_get_contents($file));

                    Photo::create([
                        'gallery_id'   => $gallery->id,
                        'title'        => pathinfo($file, PATHINFO_FILENAME),
                        'image'        => $targetPath,
                        'description'  => 'Uploaded sample photo for ' . $gallery->gallery_name,
                        'is_active'    => true,
                        'allow_cc'     => false,
                        'uploaded_by'  => $gallery->member_id ?? 1,
                    ]);
                }
            }
        }
    }
}
