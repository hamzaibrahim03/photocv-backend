<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\ClubNews;
use App\Traits\UtilityTrait;
use Carbon\Carbon;
use App\Http\Responses\ClubNewsResponse;

class ClubNewsRepository implements ClubNewsRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $clubNews = $this->getAllIndexData($request, ClubNews::with('clubNewsType')->get());
            return ClubNewsResponse::success('Club News retrieved successfully.', $clubNews);
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
        }
    }

    public function show( $id )
    {
        try {
            $clubNews = ClubNews::with('clubNewsType', 'comments')->findOrFail($id);
            if (!$clubNews) {
                return ClubNewsResponse::error('Club news not found.', 404);
            }

            return ClubNewsResponse::success('Club news retrieved successfully.', $clubNews);
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
        }
    }

    public function create(array $data, $file = null)
    {
        try {
            if ($file) {
                $imagePath = $file->store('news_images', 'public');
                $data['thumb_image'] = $imagePath;
            }

            if (!empty($data['publish_date'])) {
                $data['publish_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['publish_date'])->format('Y-m-d');
            }

            $club = Club::where('user_id', auth()->id())->first();
            if ($club) {
                $data['club_id'] = $club->id;
            }

            $clubNews = ClubNews::create($data);

            return ClubNewsResponse::success('Club news created successfully.', $clubNews, 201);
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
        }
    }

    public function update($id, array $data, $file = null)
    {
        try {
            $clubNews = ClubNews::findOrFail($id);

            // Delete old image if a new file is uploaded
            if ($file) {
                if ($clubNews->thumb_image && \Storage::disk('public')->exists($clubNews->thumb_image)) {
                    \Storage::disk('public')->delete($clubNews->thumb_image);
                }

                // Store new image and update data
                $imagePath = $file->store('news_images', 'public');
                $data['thumb_image'] = $imagePath;
            }

            // Handle publish_date format conversion
            if (!empty($data['publish_date'])) {
                $data['publish_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['publish_date'])->format('Y-m-d');
            }

            $clubNews->update($data);

            return ClubNewsResponse::success('Club news updated successfully.', $clubNews);
        } catch (\Exception $e) {
            return ClubNewsResponse::error($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 500);
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
        $randomClubNews = ClubNews::select('id', 'title', 'created_at')
            ->where('club_id', $clubId)
            ->inRandomOrder()
            ->take(5)
            ->get();

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

        // Final response
        return [
            'data' => [
                'current_month_news_count' => $newsCountThisMonth,
                'last_news_days_ago' => $lastNewsDaysFormatted,
                'total_news' => $totalClubNews,
                'random_news' => $randomClubNews,
                'calendar' => [
                    'news' => $clubNews,
                ],
            ],
        ];
    }

}
