<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Competition;
use App\Models\CompetitionMember;
use App\Models\CompetitionMembersEntry;
use Illuminate\Support\Facades\File;

class CompetitionMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competitions = Competition::all();

        foreach ($competitions as $competition) {
            // Get some random members (exclude admin/judges if needed)
            $members = User::whereNotIn('id', [1, 2])
                ->inRandomOrder()
                ->take(5)
                ->get();

            foreach ($members as $member) {
                // Create competition membership
                $compMember = CompetitionMember::create([
                    'comp_id'   => $competition->id,
                    'member_id' => $member->id,
                ]);

                // Source entries folder for this competition
                $entryPath = database_path("seeders/data/ryton-club-data/entries/{$competition->id}");

                if (File::exists($entryPath)) {
                    $images = File::files($entryPath);

                    // Limit to max allowed entries
                    $maxEntries = $competition->max_entries_print + $competition->max_entries_digital;
                    $images = collect($images)->take($maxEntries);

                    foreach ($images as $image) {
                        $entryType = $competition->max_entries_digital > 0 ? 'digital' : 'print';

                        // Destination in storage/app/public/competition_entries/
                        $fileName = uniqid() . '_' . $image->getFilename();
                        $destinationPath = storage_path("app/public/competition_entries/{$fileName}");

                        // Ensure directory exists
                        File::ensureDirectoryExists(dirname($destinationPath));

                        // Copy image if not already there
                        if (! File::exists($destinationPath)) {
                            File::copy($image->getPathname(), $destinationPath);
                        }

                        // Save entry record (public path will be /storage/competition_entries/...)
                        CompetitionMembersEntry::create([
                            'member_comp_id'    => $compMember->id,
                            'entry_type'        => $entryType,
                            'entry_image_title' => pathinfo($image->getFilename(), PATHINFO_FILENAME),
                            'entry_image'       => "competition_entries/{$fileName}",
                            'position'          => null,
                            'total_score'       => 0,
                            'is_published'      => false,
                        ]);
                    }
                }
            }
        }
    }
}
