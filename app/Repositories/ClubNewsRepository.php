<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\ClubNews;
use App\Traits\UtilityTrait;
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
            $clubNews = ClubNews::with('clubNewsType')->findOrFail($id);
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

}
