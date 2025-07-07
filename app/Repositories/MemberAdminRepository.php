<?php

namespace App\Repositories;

use App\Traits\UtilityTrait;
use App\Http\Responses\EventResponse;
use App\Http\Responses\MemberResponse;
use App\Models\Competition;
use App\Models\CompetitionMembersEntry;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Models\User;
use App\Models\Comment;
use Carbon\Carbon;
use App\Models\MemberAward;
use App\Models\MemberSocialLink;
use App\Models\MemberBrand;

class MemberAdminRepository implements MemberAdminRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allEvents( $request )
    {
        try {
            $events = $this->getAllAdminEventData($request);
            return EventResponse::success('Events retrieved successfully.', $events);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to get single event details with some extra information
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function memberSingleEvent($id)
    {
        try {
            $event = Event::with('club')->findOrFail($id);

            // Get the club
            $club = $event->club;

            // Count other members in the same club
            $memberCount = $club->users()->count();

            // Days until next event in same club
            $nextEvent = Event::where('club_id', $club->id)
                ->where('event_date', '>', now())
                ->where('id', '!=', $event->id)
                ->orderBy('event_date', 'asc')
                ->first();

            $daysUntilNextEvent = $nextEvent
                ? now()->diffInDays($nextEvent->event_date, false)
                : null;

            // Calendar events for this month
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $calendarEvents = Event::where('club_id', $club->id)
                ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
                ->orderBy('event_date', 'asc')
                ->get(['event_date', 'name'])
                ->map(fn($e) => [
                    'date' => $e->event_date->toDateString(),
                    'name' => $e->name,
                ]);

            // Recent comments on this event
            $recentComments = $event->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($comment) {
                    return [
                        'comment_id' => $comment->id,
                        'comment' => $comment->comment,
                        'user' => $comment->user->username ?? 'Unknown',
                        'created_at' => $comment->created_at->toDateTimeString(),
                    ];
                });

            // More events from same club (excluding current)
            $moreEvents = Event::where('club_id', $club->id)
                ->where('id', '!=', $event->id)
                ->latest('event_date')
                ->take(4)
                ->get(['id', 'name', 'event_date', 'featured_image'])
                ->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'name' => $e->name,
                        'event_date' => $e->event_date->toDateString(),
                        'featured_image' => $e->featured_image
                            ? asset('storage/' . $e->featured_image)
                            : null,
                    ];
                });

            return EventResponse::success('Event details retrieved successfully.', [
                'event' => $event,
                'memberCount' => $memberCount,
                'daysUntilNextEvent' => $daysUntilNextEvent,
                'calendarEvents' => $calendarEvents,
                'recentComments' => $recentComments,
                'moreEvents' => $moreEvents,
            ]);

        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to get all competitions for member admin with additional information
     * @return void
     */
    public function allCompetitions($request)
    {
        try {
            $competitions = $this->getAllAdminCompetitionData($request);
            return EventResponse::success('Competitions retrieved successfully.', $competitions);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to load single competition information
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function memberSingleCompetition($id)
    {
        try {
            $competition = Competition::findOrFail($id);

            // Get club
            $clubId = $competition->club_id;

            // Days until next competition in this club (excluding current)
            $nextCompetition = Competition::where('club_id', $clubId)
                ->where('id', '!=', $competition->id)
                ->whereDate('start_date', '>', now())
                ->orderBy('start_date')
                ->first();

            $daysUntilNextCompetition = $nextCompetition
                ? now()->diffInDays($nextCompetition->start_date, false)
                : null;

            // Calendar competitions in this month
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $calendarCompetitions = Competition::where('club_id', $clubId)
                ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->orderBy('start_date')
                ->get(['start_date', 'name'])
                ->map(fn($comp) => [
                    'date' => $comp->start_date->toDateString(),
                    'name' => $comp->name,
                ]);

            // Recent submissions on this competition
            $recentSubmissions = CompetitionMembersEntry::with([
                'competitionMember.competition:id,name,club_id',
                'competitionMember.member:id,username',
            ])
            ->whereHas('competitionMember.competition', fn($q) => $q->where('club_id', $clubId))
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($entry) {
                $competition = optional($entry->competitionMember)->competition;
                $member = optional($entry->competitionMember)->member;

                return [
                    'member_username' => $member->username ?? 'Unknown',
                    'competition_name' => $competition->name ?? 'Unknown',
                    'entry_image' => $entry->entry_image
                        ? url(Storage::url($entry->entry_image))
                        : null,
                    'submitted_at' => optional($entry->created_at)->toDateTimeString(),
                ];
            });

            // More competitions from same club (excluding current)
            $moreCompetitions = Competition::where('club_id', $clubId)
                ->where('id', '!=', $competition->id)
                ->latest('start_date')
                ->take(4)
                ->get(['id', 'name', 'start_date', 'featured_image'])
                ->map(function ($comp) {
                    return [
                        'id' => $comp->id,
                        'title' => $comp->name,
                        'start_date' => $comp->start_date->toDateString(),
                        'featured_image' => $comp->featured_image
                            ? asset('storage/' . $comp->featured_image)
                            : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Competition details retrieved successfully.',
                'data' => [
                    'competition' => $competition,
                    'daysUntilNextCompetition' => $daysUntilNextCompetition,
                    'calendarCompetitions' => $calendarCompetitions,
                    'recentSubmissions' => $recentSubmissions,
                    'moreCompetitions' => $moreCompetitions,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ]);
        }
    }

    /**
     * Method to get notices and gallery information
     * @return void
     */
    public function getMemberNotices()
    {
        $user = auth()->user();

        $notices = $user->memberNotices()
            ->with(['files', 'comments'])
            ->latest()
            ->get();

        return response()->json([
            'notices' => $notices
        ]);
    }

    /**
     * Method to get recent comments posted on the notices
     * @return \Illuminate\Database\Eloquent\Collection<int, array{comment: string|null, comment_id: int, created_at: string, notice_id: mixed, notice_title: mixed, user: mixed>|\Illuminate\Support\Collection<int, array{comment: string|null, comment_id: int, created_at: string, notice_id: mixed, notice_title: mixed, user: mixed}>}
     */
    public function getMemberNoticesRecentComments()
    {
        $recentNoticeComments = Comment::with(['user', 'memberNotice' => function ($q) {
                $q->withCount([
                    'comments as total_comments',
                    'likes as total_likes'
                ]);
            }])
            ->where('record_type', 'notice')
            ->where('is_published', true)
            ->latest()
            ->take(6)
            ->get();

        $recentCommentsFormatted = $recentNoticeComments->map(function ($comment) {
            $notice = $comment->memberNotice;

            return [
                'notice_id' => $notice->id,
                'notice_title' => $notice->title ?? 'Unknown',
                'comment_id' => $comment->id,
                'comment' => $comment->comment,
                'user' => $comment->user->username ?? 'Unknown',
                'created_at' => $comment->created_at->toDateTimeString(),
                'total_comments' => $notice->total_comments ?? 0,
                'total_likes' => $notice->total_likes ?? 0,
                'total_interactions' => ($notice->total_comments ?? 0) + ($notice->total_likes ?? 0),
            ];
        });

        // If you want to sum total interactions from all recent comments' notices:
        $totalInteractions = $recentCommentsFormatted->sum('total_interactions');

        $userId = auth()->user()->id;

        $myRecentComments = Comment::where('interacted_by', $userId)
            ->where('comment_type', 'comment')
            ->where('is_published', true)
            ->whereIn('record_type', ['notice', 'event', 'page', 'news'])
            ->latest()
            ->take(6)
            ->get();

        $myRecentLikes = Comment::where('interacted_by', $userId)
            ->where('comment_type', 'liking')
            ->whereIn('record_type', ['notice', 'event', 'page', 'news'])
            ->latest()
            ->take(6)
            ->get();

        return [
            'recent_comments_on_notices' => $recentNoticeComments,
            'total_interactions' => $totalInteractions,
            'my_recent_comments' => $myRecentComments,
            'my_recent_likes' => $myRecentLikes,
        ];
    }

    /**
     * Method to get member gallery
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getMemberGallery()
    {
        try {
            // Fetch the User model instance
            $member = User::findOrFail(auth()->user()->id);

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

    /**
     * Method to get calander events
     * @param mixed $request
     * @return \Illuminate\Database\Eloquent\Collection<int, array{date: string, name: string|null>|\Illuminate\Support\Collection<int, array{date: string, name: string|null}>}
     */
    public function getMemberEventCalanderDetails($request)
    {
        $clubIds = auth()->user()->clubs->pluck('id')->toArray();

        // Handle month/year filtering
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : Carbon::now()->endOfMonth();

        // Get events in the selected month for all user's clubs
        $eventCalanderDetails = Event::whereIn('club_id', $clubIds)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name'])
            ->map(fn($event) => [
                'date' => $event->event_date->toDateString(),
                'name' => $event->name,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $eventCalanderDetails,
        ]);
    }

    /**
     * Method to get recent events details
     * @return \Illuminate\Database\Eloquent\Collection<int, array{event_date: string, featured_image: string|null, id: int, name: string|null>|\Illuminate\Support\Collection<int, array{event_date: string, featured_image: string|null, id: int, name: string|null}>}
     */
    public function getMemberMoreEvents()
    {
        $events = Event::with(['images', 'comments.user']);

        $recentEventsList = $events
            ->latest('event_date')
            ->take(6)
            ->get(['id', 'name', 'event_date', 'featured_image', 'created_at']);

        $moreEvents = $recentEventsList->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'event_date' => $event->event_date->toDateString(),
                'featured_image' => $event->featured_image
                    ? asset('storage/' . $event->featured_image)
                    : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $moreEvents,
        ]);
    }

    /**
     * Method to get member information
     * @return User
     */
    public function getMemberInformation()
    {
        $memberId = auth()->user()->id;
        $member = User::findOrFail($memberId);

        return response()->json([
            'status' => 'success',
            'data' => $member,
        ]);
    }

    /**
     * Method to get member's recent submissions
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getMyRecentSubmissions()
    {
        $userId = auth()->user()->id;

        $recentSubmissions = CompetitionMembersEntry::with([
                'competitionMember.competition:id,name,club_id',
                'competitionMember.member:id,username',
            ])
            ->whereHas('competitionMember', function ($q) use ($userId) {
                $q->where('member_id', $userId);
            })
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($entry) {
                $competition = optional($entry->competitionMember)->competition;
                $member = optional($entry->competitionMember)->member;

                return [
                    'member_username' => $member->username ?? 'Unknown',
                    'competition_name' => $competition->name ?? 'Unknown',
                    'entry_image' => $entry->entry_image
                        ? url(Storage::url($entry->entry_image))
                        : null,
                    'submitted_at' => optional($entry->created_at)->toDateTimeString(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $recentSubmissions,
        ]);
    }

    /**
     * Method to get member's award
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getMemberAwards()
    {
        $userId = auth()->user()->id;

        $awards = MemberAward::where('member_id', $userId)
            ->latest('award_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $awards,
        ]);
    }

    /**
     * Method to get member profile dashboard extras
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getMemberProfileExtras()
    {
        $memberId = auth()->user()->id;

        // Get MemberBrands (interests and brands stored as JSON arrays)
        $memberBrands = MemberBrand::where('member_id', $memberId)->first();

        $brands = $memberBrands ? json_decode($memberBrands->brands, true) ?? [] : [];
        $interests = $memberBrands ? json_decode($memberBrands->interest, true) ?? [] : [];

        // Get Member Social Links
        $socialLinks = MemberSocialLink::where('member_id', $memberId)
            ->get()
            ->map(function ($link) {
                return [
                    'platform' => $link->social_media_name,
                    'url' => $link->social_link,
                ];
            });

        return response()->json([
            'brands' => $brands,
            'interests' => $interests,
            'social_links' => $socialLinks,
        ]);
    }

    public function addMemberNotes($data)
    {
        dd($data);
    }

    public function memberNotes($data)
    {
        
    }

}
