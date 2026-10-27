<?php

namespace App\Repositories\Member;

use App\Models\Competition;
use App\Models\CompetitionMember;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;
use App\Models\CompetitionMembersEntry;

class MemberCompetitionRepository implements MemberCompetitionRepositoryInterface
{
    use UtilityTrait;

    public function all($request)
    {
        try {
            $query = Competition::with(['judgingType', 'competitionType', 'resultMethod', 'votingMethod', 'competitionCategory', 'competitionTheme']);
            $competitions = $this->getAllCompetitionsData($request, $query, 'name');
            return CompetitionResponse::success('Competitions retrieved successfully.', $competitions);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getUpcomingCompetitions($clubId)
    {
        return Competition::query()
            ->whereDate('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();
    }

    public function joinCompetition($data)
    {
        $response = null;

        try {
            $user = auth()->user();
            $competition = Competition::findOrFail($data['comp_id']);
            $clubIds = $user->clubs->pluck('id')->toArray();

            // Ensure the competition belongs to one of the user's clubs
            if (!in_array($competition->club_id, $clubIds)) {
                $response = CompetitionResponse::error('Unauthorized to join this competition.', 403);
            } else {
                // Check if the user has already joined this competition
                $existingEntry = CompetitionMember::where('comp_id', $competition->id)
                    ->where('member_id', $user->id)
                    ->first();

                if ($existingEntry) {
                    $response = CompetitionResponse::error('You have already joined this competition.', 400);
                } else {
                    // Create a new entry for the user in the competition
                    $entry = new CompetitionMember();
                    $entry->comp_id = $competition->id;
                    $entry->member_id = $user->id;
                    $entry->save();

                    $response = CompetitionResponse::success('Successfully joined the competition.', $entry, 201);
                }
            }
        } catch (\Exception $e) {
            $response = CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

        return $response;
    }

    public function getJoinedCompetition()
    {
        $user = auth()->user();

        $competitions = $user->joinedCompetitions()
        ->get()
        ->map(function ($competition) {
            $memberCompId = $competition->pivot->id;
            $entriesCount = CompetitionMembersEntry::where('member_comp_id', $memberCompId)->count();
            $maxEntries = $competition->print_vs_digital === 'digital'
                ? $competition->max_entries_digital
                : $competition->max_entries_print;

            return [
                'competition_member_id'    => $memberCompId,
                'comp_id'                  => $competition->id,
                'name'                     => $competition->name,
                'club'                     => $competition->club?->summary(),
                'start_date'               => $competition->start_date,
                'submission_deadline'      => $competition->submission_deadline,
                'judging_start_date'       => $competition->judging_start_date,
                'judging_end_date'         => $competition->judging_end_date,
                'result_announcement_date' => $competition->result_announcement_date,
                'entries_count'            => $entriesCount,
                'max_entries'              => $maxEntries,
                'status'                   => $this->computeCompetitionStatus($competition),
            ];
        });

        return CompetitionResponse::success('Member joined competitions.', $competitions, 200);
    }

    public function getCompetitionEntries($compId)
    {
        try {
            $user = auth()->user();

            $competitionMember = CompetitionMember::where('comp_id', $compId)
                ->where('member_id', $user->id)
                ->first();

            if (!$competitionMember) {
                return CompetitionResponse::error('You have not joined this competition.', 404);
            }

            $competition = Competition::findOrFail($compId);
            $entries = CompetitionMembersEntry::where('member_comp_id', $competitionMember->id)
                ->orderBy('id')
                ->get();

            return CompetitionResponse::success('Competition entries retrieved successfully.', [
                'competition' => [
                    'id'   => $competition->id,
                    'name' => $competition->name,
                ],
                'entries' => $entries,
            ]);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function updateCompetitionEntry($entryId, $data)
    {
        try {
            $user = auth()->user();
            $entry = CompetitionMembersEntry::with('competitionMember')->findOrFail($entryId);

            if (!$entry->competitionMember || $entry->competitionMember->member_id !== $user->id) {
                return CompetitionResponse::error('Unauthorized to update this entry.', 403);
            }

            if (isset($data['entry_image_title'])) {
                $entry->entry_image_title = $data['entry_image_title'];
            }

            if (isset($data['entry_image']) && $data['entry_image']->isValid()) {
                $path = $data['entry_image']->store('competition_entries/original', 'public');
                $absolutePath = storage_path('app/public/' . $path);

                app(\App\Services\Image\ImageResizeService::class)
                    ->resize($absolutePath, 'competition_entries');

                $exifData  = $this->extractExif($absolutePath);
                $basicData = $this->extractBasicMetadata($absolutePath);
                $metadata  = array_filter($exifData) ?: $basicData;
                $metadata['dpi'] = $this->extractDpi($absolutePath);

                $entry->entry_image = $path;
                foreach ($metadata as $key => $value) {
                    $entry->$key = $value;
                }
            }

            $entry->save();

            return CompetitionResponse::success('Competition entry updated successfully.', $entry);
        } catch (\Throwable $e) {
            return CompetitionResponse::error($e->getMessage(), is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    public function deleteCompetitionEntry($entryId)
    {
        try {
            $user = auth()->user();
            $entry = CompetitionMembersEntry::with('competitionMember')->findOrFail($entryId);

            if (!$entry->competitionMember || $entry->competitionMember->member_id !== $user->id) {
                return CompetitionResponse::error('Unauthorized to delete this entry.', 403);
            }

            $entry->delete();

            return CompetitionResponse::success('Competition entry deleted successfully.', null);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    private function computeCompetitionStatus($competition)
    {
        $now = now();

        if ($competition->result_announcement_date && $now->greaterThanOrEqualTo($competition->result_announcement_date)) {
            return 'completed';
        }

        if ($competition->judging_end_date && $now->greaterThan($competition->judging_end_date)) {
            return 'completed';
        }

        if ($competition->judging_start_date && $now->greaterThanOrEqualTo($competition->judging_start_date)) {
            return 'judging';
        }

        if ($competition->submission_deadline && $now->greaterThan($competition->submission_deadline)) {
            return 'judging';
        }

        return 'open';
    }


    public function submitCompetitionEntry($data)
    {
        try {
            $user = auth()->user();

            $competitionMember = CompetitionMember::findOrFail($data['member_comp_id']);

            // Ensure ownership
            if ($competitionMember->member_id !== $user->id) {
                return CompetitionResponse::error(
                    'Unauthorized to submit entry for this competition.',
                    403
                );
            }

            // Normalise the request into a list of [title, image] pairs - the
            // frontend can submit several images (with their own titles) in
            // one request, or a single image for backward compatibility.
            $images = $data['entry_images'] ?? ($data['entry_image'] ? [$data['entry_image']] : []);
            $titles = $data['entry_image_titles'] ?? ($data['entry_image_title'] ? [$data['entry_image_title']] : []);

            $images = array_values(array_filter($images, fn ($image) => $image && $image->isValid()));

            if (empty($images)) {
                return CompetitionResponse::error(
                    'Invalid or missing entry image.',
                    400
                );
            }

            $competition = Competition::find($competitionMember->comp_id);
            if ($competition) {
                $maxEntries = $competition->print_vs_digital === 'digital'
                    ? $competition->max_entries_digital
                    : $competition->max_entries_print;

                if ($maxEntries) {
                    $existingCount = CompetitionMembersEntry::where('member_comp_id', $competitionMember->id)->count();
                    $remaining = $maxEntries - $existingCount;

                    if ($remaining <= 0) {
                        return CompetitionResponse::error(
                            'You have reached the maximum number of entries for this competition.',
                            400
                        );
                    }

                    $images = array_slice($images, 0, $remaining);
                }
            }

            $createdEntries = [];

            foreach ($images as $index => $image) {
                /* ================= STORE ORIGINAL ================= */

                $path = $image->store(
                    'competition_entries/original',
                    'public'
                );

                $absolutePath = storage_path('app/public/' . $path);

                /* ================= GENERATE SIZES ================= */

                app(\App\Services\Image\ImageResizeService::class)
                    ->resize($absolutePath, 'competition_entries');

                /* ================= METADATA ================= */

                $exifData  = $this->extractExif($absolutePath);
                $basicData = $this->extractBasicMetadata($absolutePath);
                $metadata  = array_filter($exifData) ?: $basicData;
                $metadata['dpi'] = $this->extractDpi($absolutePath);

                /* ================= SAVE ENTRY ================= */

                $entry = new CompetitionMembersEntry();
                $entry->member_comp_id     = $competitionMember->id;
                $entry->entry_type         = $data['entry_type'];
                $entry->entry_image_title  = $titles[$index] ?? ($titles[0] ?? null);
                $entry->entry_image        = $path;

                foreach ($metadata as $key => $value) {
                    $entry->$key = $value;
                }

                $entry->save();
                $createdEntries[] = $entry;
            }

            return CompetitionResponse::success(
                count($createdEntries) === 1 ? 'Competition entry submitted successfully.' : 'Competition entries submitted successfully.',
                count($createdEntries) === 1 ? $createdEntries[0] : $createdEntries,
                201
            );
        } catch (\Throwable $e) {
            return CompetitionResponse::error(
                $e->getMessage(),
                is_int($e->getCode()) && $e->getCode() > 0
                    ? $e->getCode()
                    : 500
            );
        }
    }

    /**
     * exif_read_data() only supports JPEG and TIFF - calling it on anything
     * else (PNG, GIF, SVG...) can throw rather than just warn, depending on
     * the PHP build, so every caller must check this first.
     */
    private function supportsExif($imagePath)
    {
        try {
            $type = @exif_imagetype($imagePath);
            return $type === IMAGETYPE_JPEG || $type === IMAGETYPE_TIFF_II || $type === IMAGETYPE_TIFF_MM;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function extractExif($imagePath)
    {
        if (!$this->supportsExif($imagePath)) {
            return [];
        }

        try {
            $exif = @exif_read_data($imagePath);

            if (!$exif) return [];

            return [
                'camera_model'  => $exif['Model'] ?? null,
                'lens'          => $exif['UndefinedTag:0xA434'] ?? null,
                'focal_length'  => isset($exif['FocalLength']) ? $this->formatFocalLength($exif['FocalLength']) : null,
                'aperture'      => isset($exif['FNumber']) ? $this->formatAperture($exif['FNumber']) : null,
                'shutter_speed' => isset($exif['ExposureTime']) ? $exif['ExposureTime'] . 's' : null,
                'iso'           => $exif['ISOSpeedRatings'] ?? null,
                'captured_at'   => $exif['DateTimeOriginal'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Read the image's embedded resolution (dots per inch) from EXIF, if
     * present. Most camera/phone photos carry this; screenshots and some
     * web-exported images don't, in which case this returns null.
     */
    private function extractDpi($absolutePath)
    {
        if (!$this->supportsExif($absolutePath)) {
            return null;
        }

        try {
            $exif = @exif_read_data($absolutePath);

            if (!$exif || !isset($exif['XResolution'])) {
                return null;
            }

            $resolution = $this->parseRational($exif['XResolution']);

            if ($resolution === null) {
                return null;
            }

            // ResolutionUnit: 2 = inches (already DPI), 3 = centimeters
            if (($exif['ResolutionUnit'] ?? 2) == 3) {
                $resolution *= 2.54;
            }

            return (int) round($resolution);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseRational($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value) && str_contains($value, '/')) {
            [$num, $den] = explode('/', $value);
            return (float) $den !== 0.0 ? ((float) $num / (float) $den) : null;
        }

        return null;
    }

    private function extractBasicMetadata($absolutePath)
    {
        $info = @getimagesize($absolutePath);

        return [
            'image_width'  => $info[0] ?? null,
            'image_height' => $info[1] ?? null,
            'mime_type'    => $info['mime'] ?? null,
            'file_size'    => @filesize($absolutePath) ?: null,
            'color_type'   => isset($info['channels'])
                                ? ($info['channels'] == 4 ? 'RGBA' : 'RGB')
                                : null,
            'bit_depth'    => $info['bits'] ?? null,
        ];
    }

    private function formatFocalLength($value)
    {
        if (str_contains($value, '/')) {
            [$num, $den] = explode('/', $value);
            return intval($num / $den) . 'mm';
        }
        return null;
    }

    private function formatAperture($value)
    {
        if (str_contains($value, '/')) {
            [$num, $den] = explode('/', $value);
            return 'f/' . round($num / $den, 1);
        }
        return null;
    }


}
