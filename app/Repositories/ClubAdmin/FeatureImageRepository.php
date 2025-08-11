<?php

namespace App\Repositories\ClubAdmin;
use App\Models\Page;
use App\Models\Event;
use App\Models\ClubNews;
use App\Models\MemberNotice;
use App\Models\Competition;

class FeatureImageRepository implements FeatureImageRepositoryInterface
{
    public function assignFeatureImage($data)
    {
        try {
            $modelClassMap = [
                'page'        => Page::class,
                'event'       => Event::class,
                'news'        => ClubNews::class,
                'notice'      => MemberNotice::class,
                'competition' => Competition::class,
            ];

            $modelClass = $modelClassMap[$data['model_type']] ?? null;
            if (!$modelClass) {
                return response()->json(['error' => 'Invalid model type.'], 400);
            }

            $model = $modelClass::findOrFail($data['model_id']);

            $path = $data['featured_image']->store('featured_images', 'public');

            // Save path (relative) in the model
            $model->featured_image = $path;
            $model->save();

            return response()->json([
                'message' => 'Featured image assigned successfully.',
                'featured_image' => asset('storage/' . $path),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Model not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while assigning the featured image.', 'details' => $e->getMessage()], 500);
        }
    }
}
