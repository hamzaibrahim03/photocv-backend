<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\MemberNotice;
use App\Models\MemberNoticeFile;
use App\Traits\DataTables\NoticeDataTableTrait;
use App\Http\Responses\NoticeResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class NoticeRepository implements NoticeRepositoryInterface
{
    use NoticeDataTableTrait;

    public function all( $request, $userId )
    {
        try {
            $notices = $this->getAllNotices($request, $userId);
            return NoticeResponse::success('Notices retrieved successfully.', $notices);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function show($id, $userId)
    {
        try {
            $notice = MemberNotice::with([
                'comments.user:id,username',
                'noticeType',
                'files' // 🔥 IMPORTANT
            ])->findOrFail($id);

            // Logged-in user's club
            $club = Club::where('user_id', $userId)->first();

            if (!$club || $notice->club_id !== $club->id) {
                return NoticeResponse::error(
                    'Unauthorized to view this notice.',
                    403
                );
            }

            return NoticeResponse::success(
                'Notice retrieved successfully.',
                $notice
            );

        } catch (\Exception $e) {
            return NoticeResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function create(array $data, $images = [], $documents = [])
    {
        try {
            $userId = auth()->id();
            $club = Club::where('user_id', $userId)->first();

            if ($club) {
                $data['club_id'] = $club->id;
            } else {
                $data['member_id'] = $userId;
            }

            $notice = MemberNotice::create($data);

            /* ================= IMAGE UPLOAD ================= */

            if (!empty($images) && is_array($images)) {

                $manager = new ImageManager(new Driver());

                foreach ($images as $image) {
                    if (!$image || !$image->isValid()) {
                        continue;
                    }

                    $filename = uniqid('notice_', true) . '.' . $image->getClientOriginalExtension();

                    /** ORIGINAL */
                    $originalPath = "notices/images/original/{$filename}";
                    Storage::disk('public')->put(
                        $originalPath,
                        file_get_contents($image->getRealPath())
                    );

                    $img = $manager->read($image->getRealPath());

                    /** SIZES */
                    $sizes = [
                        'thumb'  => 300,
                        'medium' => 800,
                        'large'  => 1600,
                    ];

                    foreach ($sizes as $folder => $width) {
                        $resized = clone $img;
                        $resized->scale(width: $width);

                        Storage::disk('public')->put(
                            "notices/images/{$folder}/{$filename}",
                            $resized->toJpeg(85)
                        );
                    }

                    /** DB */
                    MemberNoticeFile::create([
                        'member_notice_id' => $notice->id,
                        'file_name'        => $image->getClientOriginalName(),
                        'file_type'        => 'image',
                        'file_path'        => $originalPath, // 🔥 original only
                    ]);
                }
            }

            /* ================= DOCUMENT UPLOAD ================= */

            if (!empty($documents) && is_array($documents)) {
                foreach ($documents as $document) {
                    if (!$document) {
                        continue;
                    }

                    $documentPath = $document->store('notices/documents', 'public');

                    MemberNoticeFile::create([
                        'member_notice_id' => $notice->id,
                        'file_name'        => $document->getClientOriginalName(),
                        'file_type'        => 'document',
                        'file_path'        => $documentPath,
                    ]);
                }
            }

            return NoticeResponse::success(
                'Notice created successfully.',
                $notice,
                201
            );

        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function update($id, array $data, $images = [], $documents = [])
    {
        try {
            $notice = MemberNotice::findOrFail($id);

            $notice->update($data);

            /* ================= IMAGE UPLOAD ================= */

            if (!empty($images) && is_array($images)) {

                $manager = new ImageManager(new Driver());

                foreach ($images as $image) {
                    if (!$image || !$image->isValid()) {
                        continue;
                    }

                    $filename = uniqid('notice_', true) . '.' . $image->getClientOriginalExtension();

                    /** ORIGINAL */
                    $originalPath = "notices/images/original/{$filename}";
                    Storage::disk('public')->put(
                        $originalPath,
                        file_get_contents($image->getRealPath())
                    );

                    $img = $manager->read($image->getRealPath());

                    /** SIZES */
                    $sizes = [
                        'thumb'  => 300,
                        'medium' => 800,
                        'large'  => 1600,
                    ];

                    foreach ($sizes as $folder => $width) {
                        $resized = clone $img;
                        $resized->scale(width: $width);

                        Storage::disk('public')->put(
                            "notices/images/{$folder}/{$filename}",
                            $resized->toJpeg(85)
                        );
                    }

                    /** DB */
                    MemberNoticeFile::create([
                        'member_notice_id' => $notice->id,
                        'file_name'        => $image->getClientOriginalName(),
                        'file_type'        => 'image',
                        'file_path'        => $originalPath,
                    ]);
                }
            }

            /* ================= DOCUMENT UPLOAD ================= */

            if (!empty($documents) && is_array($documents)) {
                foreach ($documents as $document) {
                    if (!$document) {
                        continue;
                    }

                    $documentPath = $document->store('notices/documents', 'public');

                    MemberNoticeFile::create([
                        'member_notice_id' => $notice->id,
                        'file_name'        => $document->getClientOriginalName(),
                        'file_type'        => 'document',
                        'file_path'        => $documentPath,
                    ]);
                }
            }

            return NoticeResponse::success(
                'Notice updated successfully.',
                $notice
            );

        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function delete($id)
    {
        try {
            $notice = MemberNotice::findOrFail($id);

            if (!$notice) {
                return NoticeResponse::error('Notice not found or already deleted.', 404);
            }

            $notice->delete();
            return NoticeResponse::success('Notice deleted successfully.');
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getNoticeExtras($request)
    {
        $club = Club::where('user_id', auth()->id())->first();

        if (!$club) {
            return NoticeResponse::error('No club found for the current user.', 404);
        }

        $clubId = $club->id;

        // Month and Year logic
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : Carbon::now()->endOfMonth();

        // Random notices
        $randomNotices = MemberNotice::select('id', 'title', 'created_at', 'featured_image')
            ->where('club_id', $clubId)
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Monthly calendar data
        $memberNotices = MemberNotice::where('club_id', $clubId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderBy('created_at', 'asc')
            ->get(['created_at', 'title'])
            ->map(fn($e) => [
                'date' => $e->created_at->toDateString(),
                'name' => $e->title,
            ]);

        // Last notice days ago (formatted)
        $lastNotice = MemberNotice::where('club_id', $clubId)
            ->latest('created_at')
            ->first();

        $daysAgo = $lastNotice
            ? Carbon::parse($lastNotice->created_at)->startOfDay()->diffInDays(Carbon::now()->startOfDay(), false)
            : null;

        $lastNoticeDaysFormatted = $daysAgo !== null
            ? ($daysAgo < 0 ? '-' : '') . sprintf('%02d', abs($daysAgo))
            : null;

        $totalNotices = MemberNotice::where('club_id', $clubId)->count();

        $noticeCountThisMonth = MemberNotice::where('club_id', $clubId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Recent comments
        $recentComments = MemberNotice::with('comments') // Eager load comments
            ->where('club_id', $clubId)
            ->latest('created_at')
            ->take(5)
            ->get();

        // Final response
        return [
            'data' => [
                'current_month_notice_count' => $noticeCountThisMonth,
                'last_notice_days_ago' => $lastNoticeDaysFormatted,
                'total_notices' => $totalNotices,
                'random_notices' => $randomNotices,
                'recent_comments' => $recentComments,
                'calendar' => [
                    'notices' => $memberNotices,
                ],
            ],
        ];
    }

    public function getLatestNotice($clubId)
    {
        try {
            $latestNotice = MemberNotice::where('club_id', $clubId)
                ->latest('created_at')
                ->first();

            if (!$latestNotice) {
                return NoticeResponse::error('No notices found for this club.', 404);
            }

            // Transform the featured_image to full URL
            $latestNotice->featured_image = $latestNotice->featured_image
                ? asset('storage/' . $latestNotice->featured_image)
                : null;

            return NoticeResponse::success('Latest notice retrieved successfully.', $latestNotice);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getLatestNoticeWithLimit($clubId, $limit = 3)
    {
        try {
            // Fetch top 3 latest notices
            $latestNotices = MemberNotice::where('club_id', $clubId)
                ->latest('created_at')
                ->take($limit)
                ->get();

            if ($latestNotices->isEmpty()) {
                return NoticeResponse::error('No notices found for this club.', 404);
            }

            // Transform images to full URLs
            $latestNotices->transform(function ($item) {
                $item->featured_image = $item->featured_image
                    ? asset('storage/' . $item->featured_image)
                    : null;

                return $item;
            });

            return NoticeResponse::success('Latest notices retrieved successfully.', $latestNotices);

        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


}
