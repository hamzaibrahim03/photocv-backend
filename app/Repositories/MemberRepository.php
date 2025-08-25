<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\User;
use App\Traits\UtilityTrait;
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

class MemberRepository implements MemberRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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


    /**
     * Show a specific member by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $member = User::findOrFail($id);

            if (!$member) {
                return MemberResponse::error('Member not found or already deleted.', 404);
            }

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

    public function getRequestingMember($userId)
    {
        try {
            $club = auth()->user()->club;

            if (!$club) {
                return MemberResponse::error('You are not assigned to any club.');
            }

            // Get the specific user who requested to join this club
            $member = $club->users()
                        ->where('users.id', $userId)
                        ->wherePivot('status', 'pending')
                        ->first();

            if (!$member) {
                return MemberResponse::error('No pending request found for this member in your club.');
            }

            // Total pending requests in the club
            $totalPending = $club->users()->wherePivot('status', 'pending')->count();

            return MemberResponse::success(
                'Member request retrieved successfully.',
                [
                    'member' => $member,
                    'social_links' => $member->socialLinks->map(function ($link) {
                        return [
                            'social_media_name' => $link->social_media_name,
                            'social_link' => $link->social_link,
                        ];
                    }),
                    'total_pending_requests' => $totalPending
                ]
            );
        } catch (\Exception $e) {
            return MemberResponse::error('Failed to fetch member request: ' . $e->getMessage());
        }
    }


    public function getAllPendingRequests()
    {
        try {
            $club = auth()->user()->club;

            if (! $club) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to any club.',
                    'data' => [],
                    'total_pending' => 0
                ], 404);
            }

            // Get users who requested to join but not yet approved
            $pendingUsers = $club->users()->wherePivot('status', 'pending')->get();
            $totalPending = $pendingUsers->count();

            return response()->json([
                'success' => true,
                'message' => 'Pending requests retrieved successfully.',
                'data' => $pendingUsers,
                'total_pending' => $totalPending
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending requests: ' . $e->getMessage(),
                'data' => [],
                'total_pending' => 0
            ], 500);
        }
    }


    /**
     * Assign a club to a member.
     *
     * @param  int  $userId
     * @param  int  $clubId
     * @return bool
     */
    public function assignClubToUser($userId, $clubId)
    {
        $user = User::findOrFail($userId);
        $club = Club::findOrFail($clubId);

        $user->clubs()->updateExistingPivot($clubId, ['status' => 'approved', 'joined_at' => now()]);
        // $user->clubs()->syncWithoutDetaching([$clubId => ['joined_at' => now()]]);
        return true;
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

    /**
     * Process member gallery images.
     *
     * @param  array  $data
     * @return void
     */
    public function memberGalleryImages( $data )
    {
        try {
            $galleryId = $data['gallery_id'];
            $images = $data['images'];
            $titles = $data['title'] ?? [];
            $descriptions = $data['description'] ?? [];
            $isActive = isset($data['is_active']) ? (bool) $data['is_active'] : true;
            $userId = auth()->user()->id;

            foreach ($images as $index => $image) {
                // Store the image
                $path = $image->store('member-galleries', 'public');

                // Create entry in member_photos
                Photo::create([
                    'gallery_id'  => $galleryId,
                    'title'       => $titles[$index] ?? null,
                    'description' => $descriptions[$index] ?? null,
                    'image'       => $path,
                    'is_active'   => $isActive,
                    'uploaded_by' => $userId
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
            }

            if (! $recordExists) {
                return MemberResponse::error('The selected record does not exist.', 422);
            }

            $data['interacted_by'] = auth()->id();
            $comment = Comment::create($data);

            return MemberResponse::success('Comment saved successfully.', $comment, 201);
        } catch (\Exception $e) {
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
                    'photos' => $photos,
                ];
            });

            $data = [
                'member' => $member->only(['id', 'username', 'first_name', 'last_name', 'profile_image', 'email']),
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

    public function rejectClubRequest($data)
    {
        try {
            $club = auth()->user()->club;

            if (! $club) {
                return MemberResponse::error('You are not assigned to any club.');
            }

            $userId = $data['user_id'];

            // Check if this user actually has a pending request in the club
            $isPending = $club->users()
                            ->where('users.id', $userId)
                            ->wherePivot('status', 'pending')
                            ->exists();

            if (! $isPending) {
                return MemberResponse::error('No pending request found for this member in your club.');
            }

            // Update pivot to rejected
            $club->users()->updateExistingPivot($userId, [
                'status' => 'rejected',
                'rejected_at' => now(), // optional if you add column
            ]);

            return MemberResponse::success('Member request has been rejected successfully.');
        } catch (\Exception $e) {
            return MemberResponse::error('Failed to reject request: ' . $e->getMessage());
        }
    }

}
