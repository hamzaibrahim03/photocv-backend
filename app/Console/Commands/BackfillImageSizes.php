<?php

namespace App\Console\Commands;

use App\Models\ClubCoverImage;
use App\Models\ClubSetting;
use App\Models\CompetitionMembersEntry;
use App\Models\EventImage;
use App\Models\MemberNoticeFile;
use App\Services\Image\ImageResizeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-off backfill: every image-bearing table has thumb/medium/large URL
 * accessors that silently return null when the resized variant file is
 * missing from disk (rows seeded or uploaded before the resize pipeline
 * ran, or via a script that bypassed it). This regenerates every missing
 * variant through the app's real ImageResizeService rather than faking it.
 */
class BackfillImageSizes extends Command
{
    protected $signature = 'app:backfill-image-sizes';

    protected $description = 'Backfill missing thumb/medium/large image variants across all image-bearing tables';

    public function handle(): int
    {
        $this->backfill('EventImage', EventImage::whereNotNull('image')->get(), fn ($r) => $r->image, 'event_images');
        $this->backfill('MemberNoticeFile (images)', MemberNoticeFile::whereNotNull('file_path')->where('file_type', 'image')->get(), fn ($r) => $r->file_path, 'notices/images');
        $this->backfill('ClubCoverImage', ClubCoverImage::whereNotNull('image_path')->get(), fn ($r) => $r->image_path, 'uploads/clubs/cover');
        $this->backfill('ClubSetting logo', ClubSetting::whereNotNull('logo')->get(), fn ($r) => $r->logo, 'club_logos');
        $this->backfill('ClubSetting club_banner', ClubSetting::whereNotNull('club_banner')->get(), fn ($r) => $r->club_banner, 'club_banners');
        $this->backfill('ClubSetting header_img', ClubSetting::whereNotNull('header_img')->get(), fn ($r) => $r->header_img, 'uploads/clubs/header');
        $this->backfill('ClubSetting footer_img', ClubSetting::whereNotNull('footer_img')->get(), fn ($r) => $r->footer_img, 'uploads/clubs/footer');
        $this->backfill('CompetitionMembersEntry', CompetitionMembersEntry::whereNotNull('entry_image')->get(), fn ($r) => $r->entry_image, 'competition_entries');

        $this->info('All backfills complete.');
        return self::SUCCESS;
    }

    private function backfill(string $label, $rows, callable $getPath, string $baseFolder): void
    {
        $fixed = 0;
        $alreadyOk = 0;
        $missingOriginal = 0;

        foreach ($rows as $row) {
            $path = $getPath($row);
            if (!$path) {
                continue;
            }

            $filename = basename($path);
            $hasAll = Storage::disk('public')->exists("{$baseFolder}/thumb/{$filename}")
                && Storage::disk('public')->exists("{$baseFolder}/medium/{$filename}")
                && Storage::disk('public')->exists("{$baseFolder}/large/{$filename}");

            if ($hasAll) {
                $alreadyOk++;
                continue;
            }

            if (!Storage::disk('public')->exists($path)) {
                $missingOriginal++;
                continue;
            }

            // Vector formats don't need raster thumb/medium/large copies —
            // they're already infinitely scalable as-is.
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
                $alreadyOk++;
                continue;
            }

            try {
                ImageResizeService::generateSizes($path, $baseFolder);
                $fixed++;
            } catch (\Throwable $e) {
                $this->warn("  skipped {$path}: {$e->getMessage()}");
            }
        }

        $this->line("{$label}: fixed={$fixed} alreadyOk={$alreadyOk} missingOriginal={$missingOriginal}");
    }
}
