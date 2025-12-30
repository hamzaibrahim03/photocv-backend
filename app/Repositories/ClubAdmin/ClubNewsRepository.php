<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Club;
use App\Models\ClubNews;
use App\Traits\UtilityTrait;
use Carbon\Carbon;
use App\Http\Responses\ClubNewsResponse;

class ClubNewsRepository implements ClubNewsRepositoryInterface
{
    use UtilityTrait;

    public function index( $request, $clubId )
    {
        try {
            $clubNews = $this->getAllIndexData($request, ClubNews::where('club_id', $clubId)->with(['clubNewsType', 'comments']), 'title');
            return ClubNewsResponse::success('Club News retrieved successfully.', $clubNews);
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
        }
    }

    public function getNewsById( $id, $userId )
    {
        try {
            $clubNews = ClubNews::with('clubNewsType', 'comments')->findOrFail($id);

            // Get logged-in user's club
            $club = Club::where('user_id', $userId)->first();

            // Check if the event belongs to the user's club
            if (!$club || $clubNews->club_id !== $club->id) {
                return ClubNewsResponse::error('Unauthorized to view this event.', 403);
            }

            return ClubNewsResponse::success('Club news retrieved successfully.', $clubNews);
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
        }
    }

    public function create(array $data, $file = null)
    {
        try {
            if (!empty($data['publish_date'])) {
                $data['publish_date'] = Carbon::createFromFormat(
                    'd-m-Y',
                    $data['publish_date']
                )->format('Y-m-d');
            }

            $club = Club::where('user_id', auth()->id())->first();
            if ($club) {
                $data['club_id'] = $club->id;
            }

            // 🔥 FEATURED IMAGE
            if ($file && $file->isValid()) {
                $path = $file->store('news/featured/original', 'public');

                // Generate sizes
                app(\App\Services\Image\ImageResizeService::class)
                    ->resize(storage_path('app/public/' . $path), 'news/featured');

                $data['featured_image'] = $path;
            }

            $clubNews = ClubNews::create($data);

            return ClubNewsResponse::success(
                'Club news created successfully.',
                $clubNews,
                201
            );

        } catch (\Exception $e) {
            return ClubNewsResponse::error(
                $e->getMessage(),
                is_int($e->getCode()) ? $e->getCode() : 500
            );
        }
    }


    public function update($id, array $data, $file = null)
    {
        try {
            $clubNews = ClubNews::findOrFail($id);

            if (!empty($data['publish_date'])) {
                $data['publish_date'] = Carbon::createFromFormat(
                    'd-m-Y',
                    $data['publish_date']
                )->format('Y-m-d');
            }

            // 🔥 Replace featured image
            if ($file && $file->isValid()) {

                // Delete old images
                if ($clubNews->featured_image) {
                    $filename = basename($clubNews->featured_image);
                    foreach (['original', 'thumb', 'medium', 'large'] as $size) {
                        \Storage::disk('public')->delete(
                            "news/featured/{$size}/{$filename}"
                        );
                    }
                }

                $path = $file->store('news/featured/original', 'public');

                app(\App\Services\Image\ImageResizeService::class)
                    ->resize(storage_path('app/public/' . $path), 'news/featured');

                $data['featured_image'] = $path;
            }

            $clubNews->update($data);

            return ClubNewsResponse::success(
                'Club news updated successfully.',
                $clubNews
            );

        } catch (\Exception $e) {
            return ClubNewsResponse::error(
                $e->getMessage(),
                is_int($e->getCode()) ? $e->getCode() : 500
            );
        }
    }


    public function delete($id)
    {
        try {
            $clubNews = ClubNews::findOrFail($id);

            if (!$clubNews) {
                return ClubNewsResponse::error('Club news not found or already deleted.', 404);
            }

            $clubNews->delete();
            return ClubNewsResponse::success('Club news deleted successfully.');
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
        }
    }

    public function getClubNewsExtras($request)
    {
        $club = Club::where('user_id', auth()->id())->first();

        if (!$club) {
            return ClubNewsResponse::error('No club found for the current user.', 404);
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
        $randomClubNews = $this->getClubNews($clubId);

        // Monthly calendar data
        $clubNews = ClubNews::where('club_id', $clubId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderBy('created_at', 'asc')
            ->get(['created_at', 'title'])
            ->map(fn($e) => [
                'date' => $e->created_at->toDateString(),
                'name' => $e->title,
            ]);

        // Last notice days ago (formatted)
        $lastNotice = ClubNews::where('club_id', $clubId)
            ->latest('created_at')
            ->first();

        $daysAgo = $lastNotice
            ? Carbon::parse($lastNotice->created_at)->startOfDay()->diffInDays(Carbon::now()->startOfDay(), false)
            : null;

        $lastNewsDaysFormatted = $daysAgo !== null
            ? ($daysAgo < 0 ? '-' : '') . sprintf('%02d', abs($daysAgo))
            : null;

        $totalClubNews = ClubNews::where('club_id', $clubId)->count();

        $newsCountThisMonth = ClubNews::where('club_id', $clubId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Recent comments
        $recentComments = ClubNews::with('comments') // Eager load comments
            ->where('club_id', $clubId)
            ->latest('created_at')
            ->take(5)
            ->get();

        // Final response
        return [
            'data' => [
                'current_month_news_count' => $newsCountThisMonth,
                'recent_comments' => $recentComments,
                'last_news_days_ago' => $lastNewsDaysFormatted,
                'total_news' => $totalClubNews,
                'random_news' => $randomClubNews,
                'calendar' => [
                    'news' => $clubNews,
                ],
            ],
        ];
    }

    public function getClubNews($clubId)
    {
        return ClubNews::where('club_id', $clubId)
        ->latest()
        ->take(5)
        ->get();
    }

}
