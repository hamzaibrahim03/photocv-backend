<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\Page;
use App\Traits\UtilityTrait;
use App\Http\Responses\PagesResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PagesRepository implements PagesRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $pages = $this->getAllIndexData($request, Page::with('pageType')->get());
            return PagesResponse::success('Pages retrieved successfully.', $pages);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return PagesResponse::error($e->getMessage(), $statusCode);
        }
    }

    public function show( $id )
    {
        try {
            $page = Page::with('pageType', 'comments')->findOrFail($id);
            if (!$page) {
                return PagesResponse::error('Page not found.', 404);
            }

            return PagesResponse::success('Page retrieved successfully.', $page);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return PagesResponse::error($e->getMessage(), $statusCode);
        }
    }

    public function create(array $data, $file = null)
    {
        try {
            // Generate slug from title
            $data['page_slug'] = Str::slug($data['title']);

            // Ensure slug is unique
            $count = Page::where('page_slug', $data['page_slug'])->count();
            if ($count > 0) {
                $data['page_slug'] .= '-' . ($count + 1);
            }

            // Store image if provided
            if ($file) {
                $imagePath = $file->store('page_images', 'public');
                $data['thumb_image'] = $imagePath;
            }

            $club = Club::where('user_id', auth()->id())->first();
            if ($club) {
                $data['club_id'] = $club->id;
            }

            // Create the page
            $page = Page::create($data);

            return PagesResponse::success('Page created successfully.', $page, 201);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return PagesResponse::error($e->getMessage(), $statusCode);
        }
    }

    public function update($id, array $data, $file = null)
    {
        try {
            $page = Page::findOrFail($id);

            // Generate new slug if title is updated
            if (!empty($data['title']) && $data['title'] !== $page->title) {
                $data['page_slug'] = Str::slug($data['title']);

                // Ensure slug is unique
                $count = Page::where('page_slug', $data['page_slug'])->where('id', '!=', $id)->count();
                if ($count > 0) {
                    $data['page_slug'] .= '-' . ($count + 1);
                }
            }

            // Handle image update
            if ($file) {
                // Delete old image if exists
                if ($page->thumb_image && \Storage::disk('public')->exists($page->thumb_image)) {
                    \Storage::disk('public')->delete($page->thumb_image);
                }

                // Store new image
                $imagePath = $file->store('page_images', 'public');
                $data['thumb_image'] = $imagePath;
            }

            // Update the page
            $page->update($data);

            return PagesResponse::success('Page updated successfully.', $page);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return PagesResponse::error($e->getMessage(), $statusCode);
        }
    }

    public function delete($id)
    {
        try {
            $page = Page::findOrFail($id);

            if (!$page) {
                return PagesResponse::error('Page not found or already deleted.', 404);
            }

            $page->delete();
            return PagesResponse::success('Page deleted successfully.');
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() && is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return PagesResponse::error($e->getMessage(), $statusCode);
        }
    }

    public function getPagesExtras($request)
    {
        $club = Club::where('user_id', auth()->id())->first();

        if (!$club) {
            return PagesResponse::error('No club found for the current user.', 404);
        }

        $clubId = $club->id;

        // Random pages
        $randomPages = Page::select('id', 'title', 'created_at')
            ->where('club_id', $clubId)
            ->inRandomOrder()
            ->take(6)
            ->get();

        $lastPageChange = Page::where('club_id', $clubId)
            ->latest('updated_at')
            ->first();

        $daysAgo = $lastPageChange
            ? Carbon::parse($lastPageChange->updated_at)->startOfDay()->diffInDays(Carbon::now()->startOfDay(), false)
            : null;

        $lastPageChangeDaysFormatted = $daysAgo !== null
            ? ($daysAgo < 0 ? '-' : '') . sprintf('%02d', abs($daysAgo))
            : null;

        $totalDraftedPages = Page::where('club_id', $clubId)->where('status', 'draft')->count();

        $totalPages = Page::where('club_id', $clubId)->where('status', 'publish')->count();

        // Recent comments
        $recentComments = Page::with('comments') // Eager load comments
            ->where('club_id', $clubId)
            ->latest('created_at')
            ->take(5)
            ->get();

        // Final response
        return [
            'data' => [
                'total_live_pages' => $totalPages,
                'recent_comments' => $recentComments,
                'last_page_days_ago' => $lastPageChangeDaysFormatted,
                'total_drafted_pages' => $totalDraftedPages,
                'random_pages' => $randomPages,
            ],
        ];
    }

}
