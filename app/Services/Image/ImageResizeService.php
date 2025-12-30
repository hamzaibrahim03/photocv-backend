<?php

namespace App\Services\Image;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ImageResizeService
{
    public static function generateSizes(string $originalPath): array
    {
        $manager = new ImageManager(new Driver());

        $absolutePath = storage_path('app/public/' . $originalPath);
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
            $resized = clone $image;

            $resized->scale(width: $width);

            $path = "member-galleries/{$folder}/{$filename}";
            Storage::disk('public')->put($path, $resized->toJpeg(85));

            $paths[$folder] = $path;
        }

        return $paths;
    }
}
