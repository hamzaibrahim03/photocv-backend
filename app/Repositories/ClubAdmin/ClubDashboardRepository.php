<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Event;
use App\Models\MemberNotice;
use App\Models\Page;
use App\Models\Competition;
use App\Models\ClubNews;
use App\Models\User;
use App\Models\MemberAward;
use Carbon\Carbon;

class ClubDashboardRepository implements ClubDashboardRepositoryInterface
{
    public function getAuthUserDetails()
    {
        $user = auth()->user();

        return [
            'username'      => $user->username,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'role'          => $user->getRoleNames()->first(),
        ];

    }

    public function getLatestEvents(int $clubId, int $limit = 3)
    {
        $events = Event::with('images')->where('club_id', $clubId)
            ->orderBy('event_date', 'desc')
            ->limit($limit)
            ->get();

        $upcoming = Event::with('images')->where('club_id', $clubId)
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($event) {
                $remainingDays = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($event->event_date)->startOfDay(), false);
                return ['remaining_days' => $remainingDays];
            });

        return [
            'events' => $events,
            'upcoming_event' => $upcoming->isNotEmpty() ? $upcoming->first() : null,
        ];
    }

    public function getLatestNotices(int $clubId, int $limit = 3)
    {
        return MemberNotice::where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLatestClubNews(int $clubId, int $limit = 3)
    {
        return ClubNews::where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Method to return latest members
     * @param int $clubId
     * @param int $limit
     * @return array{members: \Illuminate\Database\Eloquent\Collection<int, User>, total_count: int}
     */
    public function getLatestMembers(int $clubId, int $limit = 6)
    {
        // Base query (only approved members of given club)
        $baseQuery = User::whereHas('clubs', function ($query) use ($clubId) {
            $query->where('club_id', $clubId)
                ->where('status', 'approved');
        });

        // Get latest members
        $members = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Get total count
        $totalCount = (clone $baseQuery)->count();

        return [
            'members' => $members,
            'total_count' => $totalCount,
        ];
    }

    /**
     * Method to return latest members for club public home
     * @param int $clubId
     * @param int $limit
     * @return array{members: \Illuminate\Database\Eloquent\Collection<int, User>, total_count: int}
     */
    public function getLatestMembersForHome(int $clubId, int $limit = 6)
    {
        // Base query (approved members of the given club)
        $baseQuery = User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'profile_image',
            ])
            ->whereHas('clubs', function ($query) use ($clubId) {
                $query->where('club_id', $clubId)
                    ->where('status', 'approved');
            });

        // Latest members
        $members = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Total approved members count
        // $totalCount = (clone $baseQuery)->count();

        return [
            'members' => $members,
            // 'total_count' => $totalCount,
        ];
    }


    /**
     * Method to return all members galleries
     * @param int $clubId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getMembersGallerries(int $clubId, int $limit = 10)
    {
        return User::whereHas('clubs', function ($query) use ($clubId) {
                $query->where('club_id', $clubId)
                    ->where('status', 'approved');
            })
            ->with([
                'roles:id,name',
                'galleries' => function ($galleryQuery) use ($clubId) {
                    $galleryQuery->where('club_id', $clubId)
                        ->where('is_active', true)
                        ->with(['photos' => function ($photoQuery) {
                            $photoQuery->where('is_active', true)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(1);
                        }]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }


    /**
     * Method to return all members galleries for club public home
     * @param int $clubId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getMembersGallerriesForHome(int $clubId, int $limit = 10)
    {
        return User::query()
            ->select([
                'id',        // required for relations
                'username',  // required by frontend
            ])
            ->whereHas('clubs', function ($query) use ($clubId) {
                $query->where('club_id', $clubId);
            })
            ->with([
                'galleries' => function ($galleryQuery) {
                    $galleryQuery
                        ->select([
                            'id',          // required for relation
                            'member_id',   // required for relation
                            'gallery_name'
                        ])
                        ->where('is_active', true)
                        ->with([
                            'photos' => function ($photoQuery) {
                                $photoQuery
                                    ->select([
                                        'id',          // required
                                        'gallery_id',  // required
                                        'image'
                                    ])
                                    ->where('is_active', true)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(1); // 🔥 first image only
                            }
                        ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }


    public function getCurrentMonthActivities(int $clubId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Events of current month
        $events = Event::where('club_id', $clubId)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name']) // Only get these fields
            ->map(function ($event) {
                return [
                    'date' => $event->event_date->toDateString(),
                    'name' => $event->name,
                ];
            });

        // Competitions of current month
        $competitions = Competition::query()
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_date', 'asc')
            ->get(['start_date', 'name']) // Only get these fields
            ->map(function ($competition) {
                return [
                    'date' => $competition->start_date->toDateString(),
                    'name' => $competition->name,
                ];
            });

        return [
            'events' => $events,
            'competitions' => $competitions,
        ];
    }

    /**
     * Method to get recent results
     * @param mixed $clubId
     * @return \Illuminate\Database\Eloquent\Collection<int, array{award_id: int, belongs_to_club: bool, comments: mixed, comments_count: mixed, gallery_name: string|null, image: mixed, likes: mixed, likes_count: mixed, photo_id: int|null, photo_title: string|null, uploaded_by: mixed>|\Illuminate\Support\Collection<int, array{award_id: int, belongs_to_club: bool, comments: mixed, comments_count: mixed, gallery_name: string|null, image: mixed, likes: mixed, likes_count: mixed, photo_id: int|null, photo_title: string|null, uploaded_by: mixed}>}
     */
    public function getRecentResults($clubId)
    {
        return MemberAward::with([
            'photo.gallery.member',
            'photo.uploadedBy',
            'photo.commentsOrLikes' => function ($query) {
                $query->where('is_published', true)->with('user');
            }
        ])->get()->map(function ($award) use ($clubId) {
            $photo = $award->photo;
            $gallery = $photo->gallery;

            $comments = $photo->commentsOrLikes->where('comment_type', 'comment')->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'posted_by' => $comment->user->username ?? 'Unknown',
                    'posted_by_id' => $comment->user->id ?? null,
                    'posted_at' => $comment->created_at->toDateTimeString(),
                ];
            })->values();

            $likes = $photo->commentsOrLikes->where('comment_type', 'liking')->map(function ($like) {
                return [
                    'id' => $like->id,
                    'liked_by' => $like->user->username ?? 'Unknown',
                    'liked_by_id' => $like->user->id ?? null,
                    'liked_at' => $like->created_at->toDateTimeString(),
                ];
            })->values();

            return [
                'award_id' => $award->id,
                'photo_id' => $photo->id ?? null,
                'award_standing' => $award->award_standing ?? null,
                'photo_title' => $photo->title ?? null,
                'image' => $photo->image_url ?? null,
                'gallery_name' => $gallery->gallery_name ?? null,
                'belongs_to_club' => $gallery && $gallery->club_id === $clubId,
                'uploaded_by' => $photo->uploadedBy->username ?? 'Unknown',
                'comments' => $comments,
                'likes' => $likes,
                'comments_count' => $comments->count(),
                'likes_count' => $likes->count(),
            ];
        });
    }

}