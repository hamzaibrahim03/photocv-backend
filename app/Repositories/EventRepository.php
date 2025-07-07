<?php

namespace App\Repositories;

use App\Models\Competition;
use App\Models\Club;
use App\Models\Event;
use App\Models\Comment;
use App\Models\EventImage;
use App\Traits\UtilityTrait;
use Carbon\Carbon;
use App\Http\Responses\EventResponse;

class EventRepository implements EventRepositoryInterface
{
    use UtilityTrait;

    /**
     * Get all event data with pagination and filtering.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all( $request )
    {
        try {
            $events = $this->getAllEventData($request);
            return EventResponse::success('Events retrieved successfully.', $events);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Show a specific event by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $event = Event::with('comments')->findOrFail($id);

            // Get logged-in user's club
            $club = Club::where('user_id', auth()->id())->first();

            // Check if the event belongs to the user's club
            if (!$club || $event->club_id !== $club->id) {
                return EventResponse::error('Unauthorized to view this event.', 403);
            }

            // Transform the featured_image to full URL
            $event->featured_image = $event->featured_image 
                ? asset('storage/' . $event->featured_image) 
                : null;

            $event->load('images');

            return EventResponse::success('Event retrieved successfully.', $event);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Create a new event.
     *
     * @param array $data
     * @param mixed $files
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data, $files = null)
    {
        try {
            // Get the authenticated user's club
            $club = Club::where('user_id', auth()->id())->first();

            if (!$club) {
                return EventResponse::error('No club found for the current user.', 404);
            }

            // Inject club_id into the data array
            $data['club_id'] = $club->id;

            $event = Event::create($data);

            // Handle file uploads
            if ($files) {
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('event_images', 'public');

                        EventImage::create([
                            'event_id' => $event->id,
                            'image' => $path,
                        ]);
                    }
                }
            }

            return EventResponse::success('Event created successfully.', $event, 201);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Update an existing event.
     *
     * @param int $id
     * @param array $data
     * @param mixed $files
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, array $data, $files = null)
    {
        try {
            $event = Event::findOrFail($id);

            // Validate ownership via club -> user
            $club = Club::where('user_id', auth()->id())->first();
            if (!$club || $event->club_id !== $club->id) {
                return EventResponse::error('Unauthorized access to update event.', 403);
            }

            $event->update($data);

            // Handle file uploads
            if ($files) {
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('event_images', 'public');

                        EventImage::create([
                            'event_id' => $event->id,
                            'image' => $path,
                        ]);
                    }
                }
            }

            return EventResponse::success('Event updated successfully.', $event);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Delete an event by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $event = Event::findOrFail($id);

            // Get the logged-in user's club
            $club = Club::where('user_id', auth()->id())->first();

            // Ensure the event belongs to the user's club
            if (!$club || $event->club_id !== $club->id) {
                return EventResponse::error('Unauthorized access to delete event.', 403);
            }

            $event->delete();

            return EventResponse::success('Event deleted successfully.');
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get additional event-related data for the club dashboard.
     *
     * @return array
     */
    public function getEventExtras($request)
    {
        $club = Club::where('user_id', auth()->id())->first();

        if (!$club) {
            return EventResponse::error('No club found for the current user.', 404);
        }

        $clubId = $club->id;

        // Month and Year logic
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : Carbon::now()->endOfMonth();

        // Recent comments
        $recentComments = Event::with('comments') // Eager load comments
            ->where('club_id', $clubId)
            ->latest('event_date')
            ->take(5)
            ->get();

        // Random events
        $randomEvents = Event::select('id', 'name', 'event_date', 'featured_image')
            ->where('club_id', $clubId)
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Upcoming event (closest future event)
        $upcomingEvent = Event::where('club_id', $clubId)
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->first();

        $upcoming = $upcomingEvent
            ? ['remaining_days' => Carbon::now()->startOfDay()->diffInDays(Carbon::parse($upcomingEvent->event_date)->startOfDay(), false)]
            : null;

        // Total event count
        $totalEvents = Event::where('club_id', $clubId)->count();

        // Monthly calendar data
        $events = Event::where('club_id', $clubId)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name'])
            ->map(fn($e) => [
                'date' => $e->event_date->toDateString(),
                'name' => $e->name,
            ]);

        $competitions = Competition::where('club_id', $clubId)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_date', 'asc')
            ->get(['start_date', 'name'])
            ->map(fn($c) => [
                'date' => $c->start_date->toDateString(),
                'name' => $c->name,
            ]);

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $eventCountThisMonth = Event::where('club_id', $clubId)
            ->whereDate('event_date', '>=', $today)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->count();


        // Final response
        return [
            'data' => [
                'recent_comments' => $recentComments,
                'random_events' => $randomEvents,
                'upcoming_event' => $upcoming,
                'total_events' => $totalEvents,
                'current_month_event_count' => $eventCountThisMonth,
                'calendar' => [
                    'events' => $events,
                    'competitions' => $competitions,
                ],
            ],
        ];
    }
}
