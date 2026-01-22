<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Competition;
use App\Models\CompetitionMember;
use App\Models\CompetitionMembersEntry;
use Illuminate\Support\Facades\File;
use App\Services\Image\ImageResizeService;

class CompetitionMemberSeeder extends Seeder
{
    public function run(): void
    {
        $competitions = Competition::all();

        foreach ($competitions as $competition) {

            $members = User::whereNotIn('id', [1, 2])
                ->inRandomOrder()
                ->take(5)
                ->get();

            foreach ($members as $member) {

                $compMember = CompetitionMember::create([
                    'comp_id'   => $competition->id,
                    'member_id' => $member->id,
                ]);

                $entryPath = database_path(
                    "seeders/data/ryton-club-data/entries/{$competition->id}"
                );

                if (!File::exists($entryPath)) {
                    continue;
                }

                $images = collect(File::files($entryPath));

                $maxEntries = $competition->max_entries_print
                            + $competition->max_entries_digital;

                $images = $images->take($maxEntries);

                foreach ($images as $image) {

                    $entryType = $competition->max_entries_digital > 0
                        ? 'digital'
                        : 'print';

                    /* ================= ORIGINAL PATH ================= */

                    $fileName = uniqid() . '_' . $image->getFilename();

                    $relativeOriginalPath =
                        "competition_entries/original/{$fileName}";

                    $absoluteOriginalPath =
                        storage_path("app/public/{$relativeOriginalPath}");

                    File::ensureDirectoryExists(
                        dirname($absoluteOriginalPath)
                    );

                    File::copy(
                        $image->getPathname(),
                        $absoluteOriginalPath
                    );

                    /* ================= GENERATE SIZES ================= */

                    app(ImageResizeService::class)
                        ->resize($absoluteOriginalPath, 'competition_entries');

                    /* ================= SAVE ENTRY ================= */

                    $entry = CompetitionMembersEntry::create([
                        'member_comp_id'    => $compMember->id,
                        'entry_type'        => $entryType,
                        'entry_image_title' => pathinfo(
                            $image->getFilename(),
                            PATHINFO_FILENAME
                        ),
                        'entry_image'       => $relativeOriginalPath,

                        /* ================= DUMMY EXIF ================= */

                        'camera_model'  => 'Canon EOS 5D Mark IV',
                        'lens'          => 'EF 24-70mm f/2.8L II USM',
                        'focal_length'  => '50mm',
                        'aperture'      => 'f/8',
                        'shutter_speed' => '1/125',
                        'iso'           => '200',
                        'captured_at'   => now()->subDays(rand(10, 120)),

                        /* ================= FALLBACK METADATA ================= */

                        'image_width'  => rand(3000, 6000),
                        'image_height' => rand(2000, 4000),
                        'mime_type'    => 'image/jpeg',
                        'file_size'    => rand(350000, 5500000), // bytes
                        'color_type'   => 'RGB',
                        'bit_depth'    => 8,

                        /* ================= COMPETITION DATA ================= */

                        'position'     => null,
                        'total_score'  => 0,
                        'is_published' => false,
                    ]);


                    event(new \App\Events\CompetitionEntryAdded($entry, auth()->user()));

                }
            }
        }
    }
}
