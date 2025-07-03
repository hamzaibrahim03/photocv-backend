<?php

namespace App\Repositories;

use App\Traits\UtilityTrait;
use App\Http\Responses\EventResponse;
use App\Models\Competition;
use App\Models\CompetitionMembersEntry;
use Illuminate\Support\Facades\Storage;
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


}
