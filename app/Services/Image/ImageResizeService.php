<?php

namespace App\Services\Image;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ImageResizeService
{
    /**
     * 🔥 NEW (SAFE) WRAPPER METHOD
     *
     * This is for newer code that passes an absolute path
     * and a base folder (competition_entries, news/featured, etc.)
     *
     * ✔ Does NOT affect existing generateSizes() usage
     */
    public function resize(string $absoluteOriginalPath, string $baseFolder): array
    {
        $storageRoot = realpath(storage_path('app/public'));
        $absolute    = realpath($absoluteOriginalPath);

        if (!$absolute || !str_starts_with($absolute, $storageRoot)) {
            return ['original' => null];
        }

        $relativePath = ltrim(
            str_replace($storageRoot, '', $absolute),
            DIRECTORY_SEPARATOR
        );

        return self::generateSizes($relativePath, $baseFolder);
    }


    /**
     * 🟢 EXISTING METHOD (UNCHANGED BEHAVIOUR)
     *
     * This keeps all current implementations working
     * exactly as they are today.
     */
    public static function generateSizes(
        string $originalPath,
        string $baseFolder = 'member-galleries'
    ): array {
        $manager = new ImageManager(new Driver());

        $absolutePath = storage_path('app/public/' . $originalPath);

        // Safety check (avoid fatal errors)
        if (!file_exists($absolutePath)) {
            return [
                'original' => $originalPath,
            ];
        }

        $image = $manager->read($absolutePath);
        $filename = basename($originalPath);

        $sizes = [
            'thumb'  => 300,
            'medium' => 800,
            'large'  => 1600,
        ];

        $paths = [
            'original' => $originalPath,
        ];

        foreach ($sizes as $folder => $width) {

            $directory = "{$baseFolder}/{$folder}";

            // 🔥 ENSURE DIRECTORY EXISTS
            Storage::disk('public')->makeDirectory($directory);

            $resized = clone $image;
            $resized->scale(width: $width);

            $path = "{$directory}/{$filename}";

            Storage::disk('public')->put(
                $path,
                $resized->toJpeg(85)
            );

            $paths[$folder] = $path;
        }


        return $paths;
    }
}
