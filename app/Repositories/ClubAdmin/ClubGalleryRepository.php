<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Gallery;
use App\Http\Responses\MemberResponse;
use App\Traits\UtilityTrait;


class ClubGalleryRepository implements ClubGalleryRepositoryInterface
{
    use UtilityTrait;

    /**
     * Method to get club galleries
     * @param mixed $data
     */
    public function getClubGalleries($clubAdminId)
    {
        try {
            $galleries = Gallery::with([
                'photos' => function ($q) {
                    $q->whereNull('deleted_at')->with([
                        'uploadedBy',
                        'comments' => function ($commentQuery) {
                            $commentQuery->where('is_published', true)->with('user');
                        }
                    ]);
                }
            ])->where('member_id', $clubAdminId)->get();

            // Format response
            $formattedGalleries = $galleries->map(function ($gallery) {
                $photos = $gallery->photos->map(function ($photo) {
                    $comments = $photo->comments->where('comment_type', 'comment')->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'comment' => $comment->comment,
                            'posted_by' => $comment->user->username ?? 'Unknown',
                            'posted_by_id' => $comment->user->id ?? null,
                            'posted_at' => $comment->created_at->toDateTimeString(),
                        ];
                    })->values();

                    $likes = $photo->comments->where('comment_type', 'liking')->map(function ($like) {
                        return [
                            'id' => $like->id,
                            'liked_by' => $like->user->username ?? 'Unknown',
                            'liked_by_id' => $like->user->id ?? null,
                            'liked_at' => $like->created_at->toDateTimeString(),
                        ];
                    })->values();

                    return [
                        'photo_id' => $photo->id,
                        'title' => $photo->title,
                        'image' => asset('storage/' . $photo->image),
                        'thumb_url'    => $photo->thumb_url,
                        'medium_url'   => $photo->medium_url,
                        'large_url'    => $photo->large_url,
                        'original_url' => $photo->original_url,
                        'uploaded_by' => $photo->uploadedBy->username ?? 'Unknown',
                        'comments' => $comments,
                        'likes' => $likes,
                        'comments_count' => $comments->count(),
                        'likes_count' => $likes->count(),
                    ];
                });

                return [
                    'gallery_id' => $gallery->id,
                    'gallery_name' => $gallery->gallery_name,
                    'total_photos' => $photos->count(),
                    'total_comments' => $photos->sum(fn($photo) => $photo['comments_count']),
                    'total_likes' => $photos->sum(fn($photo) => $photo['likes_count']),
                    'photos' => $photos,
                ];
            });

            return MemberResponse::success('Club galleries retrieved successfully.', $formattedGalleries, 201);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to get club galleries for club home
     * @param mixed $clubAdminId
     */
    public function getClubGalleriesForHome($clubAdminId)
    {
        try {
            $galleries = Gallery::query()
                ->select('id', 'gallery_name') // id REQUIRED for redirect
                ->where('member_id', $clubAdminId)
                ->with([
                    'photos' => function ($q) {
                        $q->select('id', 'gallery_id', 'image')
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'asc')
                        ->limit(1); // first image only
                    }
                ])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            $formattedGalleries = $galleries->map(function ($gallery) {
                return [
                    'gallery_id'   => $gallery->id,
                    'gallery_name' => $gallery->gallery_name,
                    'photos' => $gallery->photos->map(function ($photo) {
                        return [
                            'image' => asset('storage/' . $photo->image),
                            'thumb_url'    => $photo->thumb_url,
                            'medium_url'   => $photo->medium_url,
                            'large_url'    => $photo->large_url,
                            'original_url' => $photo->original_url,
                        ];
                    })->values(),
                ];
            });

            return MemberResponse::success(
                'Club galleries retrieved successfully.',
                $formattedGalleries,
                200
            );
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

    }

    /**
     * Method to get club gallery information
     * @param mixed $galleryId
     */
    public function getClubGallery($galleryId)
    {
        try {
            $gallery = Gallery::with([
                'photos' => function ($q) {
                    $q->whereNull('deleted_at')->with([
                        'uploadedBy',
                        'comments' => function ($commentQuery) {
                            $commentQuery->where('is_published', true)->with('user');
                        },
                        'likes' => function ($lq) {
                            $lq->with('user');
                        },
                    ]);
                }
            ])->findOrFail($galleryId);

            $photos = $gallery->photos->map(function ($photo) {
                $comments = $photo->comments->where('comment_type', 'comment')->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'posted_by' => $comment->user->username ?? 'Unknown',
                        'posted_by_id' => $comment->user->id ?? null,
                        'posted_at' => $comment->created_at->toDateTimeString(),
                    ];
                })->values();

                $likes = $photo->likes->map(function ($like) {
                    return [
                        'id' => $like->id,
                        'liked_by' => $like->user->username ?? 'Unknown',
                        'liked_by_id' => $like->user->id ?? null,
                        // 'liked_at' => $like->created_at->toDateTimeString(),
                    ];
                })->values();

                return [
                    'photo_id' => $photo->id,
                    'title' => $photo->title,
                    'image' => asset('storage/' . $photo->image),
                    'thumb_url'    => $photo->thumb_url,
                    'medium_url'   => $photo->medium_url,
                    'large_url'    => $photo->large_url,
                    'original_url' => $photo->original_url,

                    // Uploader details
                    'uploaded_by' => $photo->uploadedBy->username ?? 'Unknown',
                    'uploaded_by_first_name' => $photo->uploadedBy->first_name ?? 'Unknown',
                    'uploaded_by_last_name' => $photo->uploadedBy->last_name ?? 'Unknown',
                    'uploaded_by_profile_image' => $photo->uploadedBy && $photo->uploadedBy->profile_image
                        ? url('storage/' . ltrim($photo->uploadedBy->profile_image, '/'))
                        : null,

                    // EXIF metadata
                    'exif' => [
                        'camera_model'  => $photo->camera_model,
                        'lens'          => $photo->lens,
                        'focal_length'  => $photo->focal_length,
                        'aperture'      => $photo->aperture,
                        'shutter_speed' => $photo->shutter_speed,
                        'iso'           => $photo->iso,
                        'captured_at'   => $photo->captured_at,
                    ],

                    // Fallback metadata
                    'metadata' => [
                        'image_width'  => $photo->image_width,
                        'image_height' => $photo->image_height,
                        'mime_type'    => $photo->mime_type,
                        'file_size'    => $photo->file_size,
                        'color_type'   => $photo->color_type,
                        'bit_depth'    => $photo->bit_depth,
                    ],

                    // Comments & likes
                    'comments' => $comments,
                    'likes' => $likes,
                    'comments_count' => $comments->count(),
                    'likes_count' => $likes->count(),
                ];

            });

            $galleryData = [
                'gallery_id' => $gallery->id,
                'gallery_name' => $gallery->gallery_name,
                'total_photos' => $photos->count(),
                'total_comments' => $photos->sum(fn($photo) => $photo['comments_count']),
                'total_likes' => $photos->sum(fn($photo) => $photo['likes_count']),
                'photos' => $photos,
            ];

            return MemberResponse::success('Gallery details retrieved successfully.', $galleryData);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

    }

    /**
     * Method to create club gallery
     * @param mixed $data
     */
    public function createClubGallery($data)
    {
        try {
            $user = auth()->user();
            $data['member_id'] = $user->id;

            if($user->club){
                $data['club_id'] = $user->club->id;
                $data['type'] = 'club';

                // Create the gallery
                $createdGallery = Gallery::create($data);

                return MemberResponse::success('Gallery created successfully.', $createdGallery, 201);
            } else {
                return MemberResponse::error('You do not belong to this club.', 403);
            }
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
