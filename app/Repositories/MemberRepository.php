<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\User;
use App\Traits\UtilityTrait;
use App\Http\Responses\MemberResponse;
use Illuminate\Support\Str;
use App\Models\MemberGallery;
use App\Models\MemberPhoto;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberCreatedMail;
use Illuminate\Support\Facades\Storage;
use App\Models\Comment;
use App\Models\Page;
use App\Models\MemberNotice;
use App\Models\ClubNews;
use App\Models\Event;

class MemberRepository implements MemberRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function all( $request )
    {
        try {
            $members = $this->getAllIndexData($request, User::role('member'), 'username');
            return MemberResponse::success( 'Members retrieved successfully.', $members );
        } catch (\Exception $e) {
            return MemberResponse::error( $e->getMessage(), $e->getCode() ?: 500 );
        }
    }

    /**
     * Show a specific member by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id )
    {
        try {
            $member = User::findOrFail($id);
            if (!$member) {
                return MemberResponse::error('Member not found.', 404);
            }

            return MemberResponse::success('Member retrieved successfully.', $member);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
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

            $usernameBase = Str::slug($data['first_name'] . $data['last_name'], '');
            $data['username'] = $this->generateUniqueUsername($usernameBase);

            $member = User::create($data);
            $member->assignRole('member');

            // Mail::to($data['email'])->send(new MemberCreatedMail($data['email'], $randomPassword));

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
    
            // Update username only if it's changed and ensure uniqueness
            // if (!empty($data['first_name']) || !empty($data['last_name'])) {
            //     $usernameBase = Str::slug(($data['first_name'] ?? $member->first_name) . ($data['last_name'] ?? $member->last_name), '');
            //     if ($usernameBase !== $member->username) {
            //         $data['username'] = $this->generateUniqueUsername($usernameBase);
            //     }
            // }
    
            // Update member details
            $member->update($data);
    
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

        $user->clubs()->syncWithoutDetaching([$clubId => ['joined_at' => now()]]);
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
            $data['member_id'] = auth()->user()->id;

            // Create the gallery
            $createdGallery = MemberGallery::create($data);

            return MemberResponse::success('Gallery created successfully.', $createdGallery, 201);
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

            foreach ($images as $index => $image) {
                // Store the image
                $path = $image->store('member-galleries', 'public');

                // Create entry in member_photos
                MemberPhoto::create([
                    'gallery_id'  => $galleryId,
                    'title'       => $titles[$index] ?? null,
                    'description' => $descriptions[$index] ?? null,
                    'image'       => $path,
                    'is_active'   => $isActive,
                ]);
            }


            return MemberResponse::success('Images uploaded successfully.');
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

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
                        $query->where('is_active', true);
                    },
                ])
                ->get();

            // Add average image count per gallery for each member
            $members->each(function ($member) {
                $totalPhotos = 0;
                $totalGalleries = $member->galleries->count();

                foreach ($member->galleries as $gallery) {
                    $totalPhotos += $gallery->photos->count();
                }

                $member->gallery_average_photos = $totalGalleries > 0
                    ? round($totalPhotos / $totalGalleries, 2)
                    : 0;
            });

            return MemberResponse::success('Club members with galleries and photos retrieved successfully.', $members);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

    }

    public function memberGalleryDetails($memberId)
    {
        try {
            // Fetch the User model instance
            $member = User::findOrFail($memberId);

            // Load member's galleries and active photos
            $member->load([
                'galleries' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['photos' => function ($photoQuery) {
                            $photoQuery->where('is_active', true);
                        }]);
                }
            ]);

            // Calculate gallery and photo stats
            $galleryCount = $member->galleries->count();
            $totalPhotos = $member->galleries->sum(function ($gallery) {
                return $gallery->photos->count();
            });

            $averagePhotos = $galleryCount > 0
                ? round($totalPhotos / $galleryCount, 2)
                : 0;

            // Prepare response
            $data = [
                'member' => $member,
                'gallery_count' => $galleryCount,
                'total_photos' => $totalPhotos,
                'average_photos_per_gallery' => $averagePhotos,
                // 'galleries' => $member->galleries,
            ];

            return MemberResponse::success('Member galleries retrieved successfully.', $data);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }



}
