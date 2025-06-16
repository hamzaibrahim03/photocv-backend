<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\MemberNotice;
use App\Models\MemberNoticeFile;
use App\Traits\UtilityTrait;
use App\Http\Responses\NoticeResponse;
use Carbon\Carbon;

class NoticeRepository implements NoticeRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $notices = $this->getAllNoticeData($request);
            return NoticeResponse::success('Notices retrieved successfully.', $notices);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function show( $id )
    {
        try {
            $notice = MemberNotice::with('comments')->findOrFail($id);
            if (!$notice) {
                return NoticeResponse::error('Notice not found.', 404);
            }

            $notice->load('files');

            return NoticeResponse::success('Notice retrieved successfully.', $notice);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(array $data, $images = [], $documents = [])
    {
        try {

            $club = Club::where('user_id', auth()->id())->first();

            if (!$club) {
                return NoticeResponse::error('No club found for the current user.', 404);
            }

            // Inject club_id into the data array
            $data['club_id'] = $club->id;

            // Create the notice
            $notice = MemberNotice::create($data);

            // Handle Image Uploads
            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    if ($image) {
                        $imagePath = $image->store('notices/images', 'public'); 
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $image->getClientOriginalName(),
                            'file_type' => 'image',
                            'file_path' => $imagePath,
                        ]);
                    }
                }
            }

            // Handle Document Uploads
            if (!empty($documents) && is_array($documents)) {
                foreach ($documents as $document) {
                    if ($document) {
                        $documentPath = $document->store('notices/documents', 'public');
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $document->getClientOriginalName(),
                            'file_type' => 'document',
                            'file_path' => $documentPath,
                        ]);
                    }
                }
            }

            return NoticeResponse::success('Notice created successfully.', $notice, 201);
        } catch (\Exception $e) {
            return NoticeResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function update($id, array $data, $images = [], $documents = [])
    {
        try {
            $notice = MemberNotice::findOrFail($id);

            // Update notice details
            $notice->update($data);

            // Handle Image Uploads
            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    if ($image) {
                        $imagePath = $image->store('notices/images', 'public'); 
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $image->getClientOriginalName(),
                            'file_type' => 'image',
                            'file_path' => $imagePath,
                        ]);
                    }
                }
            }

            // Handle Document Uploads
            if (!empty($documents) && is_array($documents)) {
                foreach ($documents as $document) {
                    if ($document) {
                        $documentPath = $document->store('notices/documents', 'public');
                        MemberNoticeFile::create([
                            'member_notice_id' => $notice->id,
                            'file_name' => $document->getClientOriginalName(),
                            'file_type' => 'document',
                            'file_path' => $documentPath,
                        ]);
                    }
                }
            }

            return NoticeResponse::success('Notice updated successfully.', $notice);
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
        $randomNotices = MemberNotice::select('id', 'title', 'created_at')
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

}
