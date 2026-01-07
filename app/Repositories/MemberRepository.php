<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\User;
use App\Traits\DataTables\MemberDataTableTrait;
use App\Http\Responses\MemberResponse;
use Illuminate\Support\Str;
use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberCreatedMail;
use Illuminate\Support\Facades\Storage;
use App\Models\Comment;
use App\Models\Page;
use App\Models\MemberNotice;
use App\Models\ClubNews;
use App\Models\Event;
use Spatie\Permission\Models\Role;
use App\Models\MemberSocialLink;
use App\Models\ClubUser;
use Illuminate\Support\Facades\URL;
use App\Services\Image\ImageResizeService;
use App\Models\CompetitionMembersEntry;

class MemberRepository implements MemberRepositoryInterface
{
    use MemberDataTableTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function all($request)
    {
        try {
            $members = $this->getAllMemberIndexData($request);
            return MemberResponse::success('Members retrieved successfully.', $members);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getMembersByClub($clubId)
    {
        return User::with('roles')
            ->whereHas('clubs', function ($q) use ($clubId) {
                $q->where('clubs.id', $clubId);
            })
            ->get()
            ->map(function ($user) {
                return [
                    'username'   => $user->username,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                    'roles'      => $user->roles->pluck('name'),
                ];
            });
    }


    /**
     * Show a specific member by ID.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        try {
            $member = User::with(['createdBy', 'socialLinks'])->findOrFail($id);

            // Load galleries with active photos and their comments
            $member->load([
                'galleries' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['photos' => function ($photoQuery) {
                            $photoQuery->where('is_active', true)
                                ->with(['comments' => function ($q) {
                                    $q->where('is_published', true);
                                }]);
                        }]);
                },
                'competitionMembers.competition',
                'competitionMembers.entries'
            ]);

            $totalPhotos = 0;
            $totalComments = 0;
            $totalLikes = 0;
            $totalGalleries = $member->galleries->count();

            foreach ($member->galleries as $gallery) {
                foreach ($gallery->photos as $photo) {
                    $totalPhotos++;

                    foreach ($photo->comments as $comment) {
                        if ($comment->comment_type === 'comment') {
                            $totalComments++;
                        } elseif ($comment->comment_type === 'liking') {
                            $totalLikes++;
                        }
                    }
                }
            }

            $member->gallery_total_photos = $totalPhotos;
            $member->gallery_total_comments = $totalComments;
            $member->gallery_total_likes = $totalLikes;
            $member->gallery_average_photos = $totalGalleries > 0
                ? round($totalPhotos / $totalGalleries, 2)
                : 0;

            return MemberResponse::success('Member retrieved successfully.', $member);

        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), (int) ($e->getCode() ?: 500));
        }
    }


    /**
     * Create a new member.
     *
     * @param  array  $data
     * @param  mixed  $file
     */
    public function create(array $data, $file = null)
    {
        try {
            $randomPassword = Str::random(10);
            $data['password'] = bcrypt($randomPassword);
            if ($file) {
                $data['profile_image'] = $file->store('profile_images', 'public');
            }

            $nameParts = preg_split('/\s+/', trim($data['full_name']), 2);
            $data['first_name'] = $nameParts[0] ?? null;
            $data['last_name']  = $nameParts[1] ?? null;

            $usernameBase = Str::slug($data['full_name'], '');
            $data['username'] = $this->generateUniqueUsername($usernameBase);

            $data['created_by'] = auth()->id();

            $member = User::create($data);
            $role = Role::find($data['role_id']);
            if ($role) {
                $member->assignRole($role);
            }

            // Add member to club with approved status
            ClubUser::create([
                'user_id' => $member->id,
                'club_id' => auth()->user()->club->id,
                'status'  => 'approved',
                'joined_at' => now(),
            ]);

            // Mail::to($data['email'])->send(new MemberCreatedMail($data['email'], $randomPassword));

            if (!empty($data['member_social_media'])) {
                foreach ($data['member_social_media'] as $social) {
                    MemberSocialLink::create([
                        'member_id' => $member->id ?? null,
                        'social_media_name' => $social['social_media_name'],
                        'social_link' => $social['social_link'],
                    ]);
                }
            }

            return MemberResponse::success('Member created successfully.', $member, 201);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Update an existing member.
     *
     * @param  int  $id
     * @param  array  $data
     * @param  mixed  $file
     */
    public function update($id, array $data, $file = null)
    {
        try {
            unset($data['password']);
            unset($data['email']);
            unset($data['username']);

            $member = User::findOrFail($id);
    
            // Handle profile image update
            if ($file) {
                // Delete old image if exists
                if ($member->profile_image) {
                    Storage::disk('public')->delete($member->profile_image);
                }
                $data['profile_image'] = $file->store('profile_images', 'public');
            }

            $nameParts = preg_split('/\s+/', trim($data['full_name']), 2);
            $data['first_name'] = $nameParts[0] ?? null;
            $data['last_name']  = $nameParts[1] ?? null;
    
            // Update username only if it's changed and ensure uniqueness
            // if (!empty($data['first_name']) || !empty($data['last_name'])) {
            //     $usernameBase = Str::slug(($data['first_name'] ?? $member->first_name) . ($data['last_name'] ?? $member->last_name), '');
            //     if ($usernameBase !== $member->username) {
            //         $data['username'] = $this->generateUniqueUsername($usernameBase);
            //     }
            // }
    
            // Update member details
            $member->update($data);

            if (!empty($data['member_social_media'])) {
                // Delete old links
                MemberSocialLink::where('member_id', $member->id)->delete();

                // Insert new ones
                foreach ($data['member_social_media'] as $social) {
                    MemberSocialLink::create([
                        'member_id' => $member->id,
                        'social_media_name' => $social['social_media_name'],
                        'social_link' => $social['social_link'],
                    ]);
                }
            }
    
            return MemberResponse::success('Member updated successfully.', $member);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Delete a member by ID.
     *
     * @param  int  $id
     */
    public function delete($id)
    {
        try {
            $member = User::findOrFail($id);

            // Optional: check if member exists (already handled by findOrFail)
            if (!$member) {
                return MemberResponse::error('Member not found or already deleted.', 404);
            }

            $member->clubs()->detach();

            // Optional: remove social links
            $member->socialLinks()->delete();

            // Delete the member (soft delete if your model uses SoftDeletes)
            $member->delete();

            return MemberResponse::success('Member deleted successfully.');
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    /**
     * Ensure unique username for the member.
     *
     * @param  string  $usernameBase
     * @return string
     */
    private function generateUniqueUsername($usernameBase)
    {
        $username = $usernameBase;
        $count = 1;

        while (User::where('username', $username)->exists()) {
            $username = $usernameBase . $count;
            $count++;
        }

        return $username;
    }

    public function requestToJoinClub($userId, $clubId)
    {
        try {
            $user = User::findOrFail($userId);
            $club = Club::findOrFail($clubId);

            // Check if the user has already requested or joined
            $existing = $user->clubs()->where('club_id', $clubId)->first();
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'You have already requested to join this club or are already a member.'
                ];
            }

            // Add to pivot table with 'pending' status
            $user->clubs()->syncWithoutDetaching([
                $clubId => ['status' => 'pending', 'joined_at' => now()]
            ]);

            return [
                'success' => true,
                'message' => 'Your request to join the club has been submitted successfully.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to submit request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a new gallery for the member.
     *
     * @param  array  $data
     * @return \Illuminate\Http\Response
     */
    public function createGallery($data) {
        try {
            $user = auth()->user();
            $data['member_id'] = $user->id;
            $clubExists = $user->clubs->contains('id', $data['club_id']);

            if($clubExists){
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

    private function extractBasicMetadata($absolutePath)
    {
        $info = @getimagesize($absolutePath);

        return [
            'image_width'  => $info[0] ?? null,
            'image_height' => $info[1] ?? null,
            'mime_type'    => $info['mime'] ?? null,
            'file_size'    => @filesize($absolutePath) ?: null,

            // Safe fallback handling
            'color_type'   => isset($info['channels'])
                                ? ($info['channels'] == 4 ? 'RGBA' : 'RGB')
                                : null,

            'bit_depth'    => $info['bits'] ?? null,
        ];
    }

    private function extractExif($imagePath)
    {
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
        } catch (\Exception $e) {
            return [];
        }
    }

    private function formatFocalLength($value)
    {
        // focal length comes as fraction "100/1"
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


    /**
     * Process member gallery images.
     *
     * @param  array  $data
     * @return void
     */
    public function memberGalleryImages($data)
    {
        try {
            $galleryId = $data['gallery_id'];
            $images = $data['images'];
            $titles = $data['title'] ?? [];
            $descriptions = $data['description'] ?? [];
            $isActive = isset($data['is_active']) ? (bool) $data['is_active'] : true;
            $userId = auth()->id();

            foreach ($images as $index => $image) {

                // 1️⃣ Store ORIGINAL
                $originalPath = $image->store('member-galleries/original', 'public');

                // 2️⃣ Generate sizes (ONCE)
                $paths = ImageResizeService::generateSizes($originalPath);

                // 3️⃣ Save DB record
                Photo::create([
                    'gallery_id'  => $galleryId,
                    'title'       => $titles[$index] ?? null,
                    'description' => $descriptions[$index] ?? null,

                    // store ORIGINAL path as primary
                    'image'       => $paths['original'],

                    // optional: store JSON sizes (recommended)
                    'image_sizes' => json_encode([
                        'thumb'  => $paths['thumb'],
                        'medium' => $paths['medium'],
                        'large'  => $paths['large'],
                    ]),

                    'is_active'   => $isActive,
                    'uploaded_by' => $userId,
                ]);
            }

            return MemberResponse::success('Images uploaded successfully.');

        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to post comments or likes
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function postCommentOrLikes($data){
        try {

            $recordExists = false;

            switch ($data['record_type']) {
                case 'page':
                    $recordExists = Page::where('id', $data['record_id'])->exists();
                    break;
                case 'notice':
                    $recordExists = MemberNotice::where('id', $data['record_id'])->exists();
                    break;
                case 'event':
                    $recordExists = Event::where('id', $data['record_id'])->exists();
                    break;
                case 'news':
                    $recordExists = ClubNews::where('id', $data['record_id'])->exists();
                    break;
                case 'photo':
                    $recordExists = Photo::where('id', $data['record_id'])->exists();
                    break;
                case 'competition_entry':
                    $recordExists = CompetitionMembersEntry::where('id', $data['record_id'])->exists();
                    break;
            }

            if (! $recordExists) {
                return MemberResponse::error('The selected record does not exist.', 422);
            }

            $data['interacted_by'] = auth()->id();
            $data['is_viewed'] = 0;
            $comment = Comment::create($data);

            return MemberResponse::success('Comment saved successfully.', $comment, 201);
        } catch (\Exception $e) {
            \Log::error('Post comment error', [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to load all member galleries
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function membersGalleries()
    {
        try {
            $clubId = auth()->user()->club->id;

            $members = User::whereHas('clubs', function ($query) use ($clubId) {
                    $query->where('clubs.id', $clubId);
                })
                ->withCount(['galleries']) // gallery count
                ->with([
                    'galleries.photos' => function ($query) {
                        $query->where('is_active', true)
                            ->with(['comments' => function ($q) {
                                $q->where('is_published', true);
                            }]);
                    },
                ])
                ->get();

            $members->each(function ($member) {
                $totalPhotos = 0;
                $totalComments = 0;
                $totalLikes = 0;

                $totalGalleries = $member->galleries->count();

                foreach ($member->galleries as $gallery) {
                    foreach ($gallery->photos as $photo) {
                        $totalPhotos++;

                        foreach ($photo->comments as $comment) {
                            if ($comment->comment_type === 'comment') {
                                $totalComments++;
                            } elseif ($comment->comment_type === 'liking') {
                                $totalLikes++;
                            }
                        }
                    }
                }

                $member->gallery_total_photos = $totalPhotos;
                $member->gallery_total_comments = $totalComments;
                $member->gallery_total_likes = $totalLikes;
                $member->gallery_average_photos = $totalGalleries > 0
                    ? round($totalPhotos / $totalGalleries, 2)
                    : 0;
            });

            return MemberResponse::success('Club members with galleries retrieved successfully.', $members);

        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), (int) ($e->getCode() ?: 500));
        }
    }

    /**
     * Method to get single member gallery details
     * @param mixed $memberId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function memberGalleryDetails($memberId)
    {
        try {
            $member = User::findOrFail($memberId);

            $member->load([
                'galleries' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['photos' => function ($photoQuery) {
                            $photoQuery->where('is_active', true)
                                ->with([
                                    'comments' => function ($q) {
                                        $q->where('is_published', true)->orderBy('created_at', 'desc');
                                    },
                                    'comments.user:id,username'
                                ]);
                        }]);
                }
            ]);

            $galleryCount = $member->galleries->count();
            $totalPhotos = $member->galleries->sum(fn($gallery) => $gallery->photos->count());
            $averagePhotos = $galleryCount > 0 ? round($totalPhotos / $galleryCount, 2) : 0;

            $galleriesData = $member->galleries->map(function ($gallery) {
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
                        'created_at' => $photo->created_at,
                        'comments' => $comments,
                        'likes' => $likes,
                        'comments_count' => $comments->count(),
                        'likes_count' => $likes->count(),
                    ];
                });

                // Calculate totals for this gallery
                $totalGalleryPhotos = $photos->count();
                $totalGalleryComments = $photos->sum(fn($photo) => $photo['comments_count']);
                $totalGalleryLikes = $photos->sum(fn($photo) => $photo['likes_count']);

                return [
                    'gallery_id' => $gallery->id,
                    'gallery_name' => $gallery->gallery_name,
                    'total_photos' => $totalGalleryPhotos,
                    'total_comments' => $totalGalleryComments,
                    'total_likes' => $totalGalleryLikes,
                    'created_at' => $gallery->created_at,
                    'photos' => $photos,
                ];
            });

            $storedPath = $member->profile_image;

            if ($storedPath && !str_contains($storedPath, 'profile_images/')) {
                $storedPath = 'profile_images/' . $storedPath;
            }

            $data = [
                'member' => [
                    'username'      => $member->username,
                    'first_name'    => $member->first_name,
                    'last_name'     => $member->last_name,
                    'email'         => $member->email,
                    'profile_image' => $storedPath
                        ? URL::to('storage/' . $storedPath)
                        : null,

                    // Role (Spatie)
                    'role' => $member->roles->pluck('name')->first(),

                    // Social Links
                    'social_links' =>  ($member->socialLinks ?? collect())->map(function ($link) {
                        return [
                            'platform' => $link->social_media_name,
                            'url'      => $link->social_link,
                        ];
                    }),
                ],
                'gallery_count' => $galleryCount,
                'total_photos' => $totalPhotos,
                'average_photos_per_gallery' => $averagePhotos,
                'galleries' => $galleriesData,
            ];

            return MemberResponse::success('Member galleries retrieved successfully.', $data);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

    }

    /**
     * Method to update member's profile
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profileUpdate($data)
    {
        $user = auth()->user();

        // Update user
        $user->update(array_filter([
            // 'username' => $data['username'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => isset($data['password']) ? bcrypt($data['password']) : null,
            'tag_line' => $data['tag_line'] ?? null,
            'about' => $data['about'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]));

        // Update member
        if ($user->member) {
            $user->member->update(array_filter([
                'domain_name' => $data['member']['domain_name'] ?? null,
                'color_theme' => $data['member']['color_theme'] ?? null,
                'cover_image' => $data['member']['cover_image'] ?? null,
                'font' => $data['member']['font'] ?? null,
                'profile_privacy' => $data['member']['profile_privacy'] ?? null,
                'footer_text' => $data['member']['footer_text'] ?? null,
                'social_links_visibility' => $data['member']['social_links_visibility'] ?? [],
            ]));
        }

        // Update or create contact
        if (!empty($data['member_contact'])) {
            $user->member->memberContact()->updateOrCreate(
                ['member_id' => $user->member->id],
                [
                    'email' => $data['member_contact']['email'] ?? null,
                    'phone' => $data['member_contact']['phone'] ?? null,
                    'address' => $data['member_contact']['address'] ?? null,
                ]
            );
        }

        // Update or create brands/interests
        if (!empty($data['member_brands'])) {
            $user->member->memberBrand()->updateOrCreate(
                ['member_id' => $user->member->id],
                [
                    'brands' => $data['member_brands']['brands'] ?? [],
                    'interest' => $data['member_brands']['interest'] ?? [],
                ]
            );
        }

        // Replace all social links
        if (!empty($data['member_social_media'])) {
            $user->member->memberSocialLinks()->delete();

            foreach ($data['member_social_media'] as $social) {
                $user->member->memberSocialLinks()->create([
                    'social_media_name' => $social['social_media_name'],
                    'social_link' => $social['social_link'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->load([
                'member',
                'member.memberContact',
                'member.memberBrand',
                'member.memberSocialLinks'
            ]),
        ]);
    }

    /**
     * Method to return member's joined clubs
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getJoinedClubs()
    {
        try{
            $clubs = auth()->user()->clubs;
            return MemberResponse::success('Member clubs retrieved successfully.', $clubs);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

}
