<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Competition;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventImage;
use App\Traits\DataTables\EventDataTableTrait;
use Carbon\Carbon;
use App\Http\Responses\EventResponse;
use App\Models\User;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class EventRepository implements EventRepositoryInterface
{
    use EventDataTableTrait;

    /**
     * Get all event data with pagination and filtering.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all( $request, $userId )
    {
        try {
            $events = $this->getAllEventData($request, $userId);
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
    public function getEventById($id, $userId)
    {
        try {
            $event = Event::with(['types', 'tags', 'comments', 'images'])->findOrFail($id);

            $club = Club::where('user_id', $userId)->first();

            if (!$club || $event->club_id !== $club->id) {
                return EventResponse::error('Unauthorized to view this event.', 403);
            }

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
            $club = Club::where('user_id', auth()->id())->first();

            if (!$club) {
                return EventResponse::error('No club found for the current user.', 404);
            }

            $data['club_id'] = $club->id;

            $eventTypes = $data['event_types'] ?? [];
            $eventTags  = $data['event_tags'] ?? [];

            unset($data['event_types'], $data['event_tags']);

            $event = Event::create($data);

            if (!empty($eventTypes)) {
                $event->types()->sync($eventTypes);
            }

            if (!empty($eventTags)) {
                $event->tags()->sync($eventTags);
            }

            /* ================= IMAGE HANDLING ================= */

            if ($files) {

                $manager = new ImageManager(new Driver());

                foreach ($files as $file) {

                    if (!$file->isValid()) {
                        continue;
                    }

                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                    /** 1️⃣ Store ORIGINAL */
                    $originalPath = "event_images/original/{$filename}";
                    Storage::disk('public')->put(
                        $originalPath,
                        file_get_contents($file->getRealPath())
                    );

                    $image = $manager->read($file->getRealPath());

                    /** 2️⃣ Generate sizes */
                    $sizes = [
                        'thumb'  => 300,
                        'medium' => 800,
                        'large'  => 1600,
                    ];

                    foreach ($sizes as $folder => $width) {
                        $resized = clone $image;
                        $resized->scale(width: $width);

                        Storage::disk('public')->put(
                            "event_images/{$folder}/{$filename}",
                            $resized->toJpeg(85)
                        );
                    }

                    /** 3️⃣ Save DB record */
                    EventImage::create([
                        'event_id' => $event->id,
                        'image'    => $originalPath, // 🔥 original only
                    ]);
                }
            }

            return EventResponse::success(
                'Event created successfully.',
                $event->load(['types', 'tags', 'images']),
                201
            );

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

            $club = Club::where('user_id', auth()->id())->first();
            if (!$club || $event->club_id !== $club->id) {
                return EventResponse::error('Unauthorized access to update event.', 403);
            }

            $eventTypes = $data['event_types'] ?? [];
            $eventTags  = $data['event_tags'] ?? [];

            unset($data['event_types'], $data['event_tags']);

            $event->update($data);

            if (!empty($eventTypes)) {
                $event->types()->sync($eventTypes);
            }

            if (!empty($eventTags)) {
                $event->tags()->sync($eventTags);
            }

            /* ================= IMAGE HANDLING ================= */

            if ($files) {

                $manager = new ImageManager(new Driver());

                foreach ($files as $file) {

                    if (!$file->isValid()) {
                        continue;
                    }

                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                    /** ORIGINAL */
                    $originalPath = "event_images/original/{$filename}";
                    Storage::disk('public')->put(
                        $originalPath,
                        file_get_contents($file->getRealPath())
                    );

                    $image = $manager->read($file->getRealPath());

                    /** SIZES */
                    $sizes = [
                        'thumb'  => 300,
                        'medium' => 800,
                        'large'  => 1600,
                    ];

                    foreach ($sizes as $folder => $width) {
                        $resized = clone $image;
                        $resized->scale(width: $width);

                        Storage::disk('public')->put(
                            "event_images/{$folder}/{$filename}",
                            $resized->toJpeg(85)
                        );
                    }

                    /** DB */
                    EventImage::create([
                        'event_id' => $event->id,
                        'image'    => $originalPath,
                    ]);
                }
            }

            return EventResponse::success(
                'Event updated successfully.',
                $event->load(['types', 'tags', 'images'])
            );

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
        $recentComments = Event::with('comments')
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
        $upcoming = $this->getUpcomingEventRemainingDays($clubId);

        // Total event count
        $totalEvents = $this->getTotalEvents($clubId);

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

    /**
     * Get total number of events for a club.
     *
     * @param int $clubId
     * @return int
     */
    public function getTotalEvents($clubId)
    {
        return Event::where('club_id', $clubId)->count();
    }

    /**
     * Get remaining days until the next upcoming event for a club.
     *
     * @param int $clubId
     * @return array|null
     */
    public function getUpcomingEventRemainingDays($clubId)
    {
        $upcomingEvents = $this->getUpcomingEvents($clubId);

        $firstEvent = $upcomingEvents->first();

        return $firstEvent
            ? [
                'remaining_days' => Carbon::now()
                    ->startOfDay()
                    ->diffInDays(
                        Carbon::parse($firstEvent->event_date)->startOfDay(),
                        false
                    )
            ]
            : null;
    }

    /**
     * Method to get upcoming events for a specific club.
     * @param mixed $clubId
     */
    public function getUpcomingEvents($clubId)
    {
        return Event::with('images')
            ->where('club_id', $clubId)
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->get();
    }


    public function getUpcomingEventsForHome($clubId)
    {
        return Event::query()
        ->select([
            'id',
            'featured_image',
            'name',
            'event_date',
            'speaker',
            'created_at',
        ])
        ->where('club_id', $clubId)
        ->whereDate('event_date', '>=', now())
        ->orderBy('event_date', 'asc')
        ->limit(3)
        ->get();
    }

    public function getEventsByLlimit($clubId, $limit = null)
    {
        $query = Event::with('images')
            ->where('club_id', $clubId)
            ->orderBy('event_date', 'asc');

        if (!is_null($limit)) {
            $query->limit((int) $limit);
        }

        return $query->get();
    }


    /**
     * Method to get upcoming events for all clubs the user is associated with.
     * @param mixed $userId
     */
    public function getUpcomingEventsForAllClubs($userId)
    {
        $user = User::with('clubs.events')->findOrFail($userId);

        $clubsWithUpcomingEvents = $user->clubs->map(function ($club) {
            // Get the nearest upcoming event
            $nextEvent = $club->events()
                ->whereDate('event_date', '>=', now())
                ->orderBy('event_date', 'asc')
                ->first();

            return [
                'club_name'     => $club->club_name,
                'next_event'    => $nextEvent ? $nextEvent->event_date->toDateString() : null,
                'days_remaining'=> $nextEvent ? now()->diffInDays($nextEvent->event_date, false) : null,
            ];
        });

        return EventResponse::success(
            'Data reterived successfully.',
            $clubsWithUpcomingEvents
        );
    }
}
