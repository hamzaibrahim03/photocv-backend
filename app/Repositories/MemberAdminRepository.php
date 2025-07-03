<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\User;
use App\Traits\UtilityTrait;
use App\Http\Responses\EventResponse;
use Illuminate\Support\Str;
use App\Models\MemberGallery;
use App\Models\MemberPhoto;
use Illuminate\Support\Facades\Storage;
use App\Models\Comment;
use App\Models\Page;
use App\Models\MemberNotice;
use App\Models\ClubNews;
use App\Models\Event;

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

}
