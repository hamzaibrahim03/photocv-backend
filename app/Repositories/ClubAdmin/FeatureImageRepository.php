<?php

namespace App\Repositories\ClubAdmin;
use App\Models\Page;
use App\Models\Event;
use App\Models\ClubNews;
use App\Models\MemberNotice;
use App\Models\Competition;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

            if (
                !isset($data['featured_image']) ||
                !$data['featured_image']->isValid()
            ) {
                return response()->json(['error' => 'Invalid image uploaded.'], 422);
            }

            $file = $data['featured_image'];

            $manager = new ImageManager(new Driver());

            $filename = uniqid('featured_', true) . '.' . $file->getClientOriginalExtension();

            /** ======================
             * 1️⃣ Store ORIGINAL
             * ====================== */
            $originalPath = "featured_images/original/{$filename}";
            Storage::disk('public')->put(
                $originalPath,
                file_get_contents($file->getRealPath())
            );

            $image = $manager->read($file->getRealPath());

            /** ======================
             * 2️⃣ Generate sizes
             * ====================== */
            $sizes = [
                'thumb'  => 300,
                'medium' => 800,
                'large'  => 1600,
            ];

            foreach ($sizes as $folder => $width) {
                $resized = clone $image;
                $resized->scale(width: $width);

                Storage::disk('public')->put(
                    "featured_images/{$folder}/{$filename}",
                    $resized->toJpeg(85)
                );
            }

            /** ======================
             * 3️⃣ Save original path
             * ====================== */
            $model->featured_image = $originalPath;
            $model->save();

            return response()->json([
                'message' => 'Featured image assigned successfully.',
                'featured_image' => [
                    'thumb_url'    => Storage::disk('public')->exists("featured_images/thumb/{$filename}")
                        ? URL::to('storage/featured_images/thumb/' . $filename)
                        : null,

                    'medium_url'   => Storage::disk('public')->exists("featured_images/medium/{$filename}")
                        ? URL::to('storage/featured_images/medium/' . $filename)
                        : null,

                    'large_url'    => Storage::disk('public')->exists("featured_images/large/{$filename}")
                        ? URL::to('storage/featured_images/large/' . $filename)
                        : null,

                    'original_url' => URL::to('storage/' . $originalPath),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Model not found.'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while assigning the featured image.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

}
