<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\{Gallery, Photo};

class PhotoSeeder extends Seeder
{
    public function run(): void
    {
        $baseClubPath   = database_path('seeders/data/ryton-club-data/galleries');
        $baseMemberPath = database_path('seeders/data/ryton-club-data/member-galleries');

        $manager = new ImageManager(new Driver());

        $galleries = Gallery::with('member')
            ->where('club_id', 1)
            ->get();

        foreach ($galleries as $gallery) {

            $galleryFolder = null;

            if ($gallery->type === 'club') {
                $galleryFolder = $baseClubPath . '/' . $gallery->gallery_name;
            } elseif ($gallery->type === 'member' && $gallery->member) {
                $galleryFolder = $baseMemberPath . '/' . $gallery->member->username;
            }

            if (!$galleryFolder || !is_dir($galleryFolder)) {
                continue;
            }

            $files = glob($galleryFolder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

            foreach ($files as $file) {

                $filename = basename($file);

                /* ===============================
                 * 1️⃣ STORE ORIGINAL
                 * =============================== */
                $originalPath = "member-galleries/original/{$filename}";
                Storage::disk('public')->put(
                    $originalPath,
                    file_get_contents($file)
                );

                $image = $manager->read($file);

                /* ===============================
                 * 2️⃣ GENERATE IMAGE SIZES
                 * =============================== */
                $sizes = [
                    'thumb'  => 300,
                    'medium' => 800,
                    'large'  => 1600,
                ];

                foreach ($sizes as $folder => $width) {
                    $resized = clone $image;

                    $resized->scale(width: $width);

                    $path = "member-galleries/{$folder}/{$filename}";
                    Storage::disk('public')->put(
                        $path,
                        $resized->toJpeg(85)
                    );
                }

                /* ===============================
                 * 3️⃣ DUMMY EXIF + METADATA
                 * =============================== */
                $width  = $image->width();
                $height = $image->height();

                Photo::create([
                    'gallery_id'  => $gallery->id,
                    'title'       => pathinfo($file, PATHINFO_FILENAME),
                    'image'       => $originalPath, // 🔥 ORIGINAL ONLY
                    'description' => 'Uploaded sample photo for ' . $gallery->gallery_name,
                    'is_active'   => true,
                    'allow_cc'    => false,
                    'uploaded_by' => $gallery->member_id ?? 1,

                    /* ---------- EXIF ---------- */
                    'camera_model'  => 'Canon EOS 5D Mark IV',
                    'lens'          => 'EF 24-70mm f/2.8L II USM',
                    'focal_length'  => '35mm',
                    'aperture'      => 'f/8',
                    'shutter_speed' => '1/125',
                    'iso'           => '200',
                    'captured_at'   => now()->subDays(rand(5, 180)),

                    /* ------ FALLBACK META ----- */
                    'image_width'  => $width,
                    'image_height' => $height,
                    'mime_type'    => mime_content_type($file),
                    'file_size'    => filesize($file),
                    'color_type'   => 'RGB',
                    'bit_depth'    => 8,
                ]);
            }
        }
    }
}
