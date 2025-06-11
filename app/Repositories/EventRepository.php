<?php

namespace App\Repositories;

use App\Models\Club;
use App\Models\Event;
use App\Models\EventImage;
use App\Traits\UtilityTrait;
use App\Http\Responses\EventResponse;

class EventRepository implements EventRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        try {
            $events = $this->getAllEventData($request);
            return EventResponse::success('Events retrieved successfully.', $events);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::findOrFail($id);

            // Get logged-in user's club
            $club = Club::where('user_id', auth()->id())->first();

            // Check if the event belongs to the user's club
            if (!$club || $event->club_id !== $club->id) {
                return EventResponse::error('Unauthorized to view this event.', 403);
            }

            $event->load('images');

            return EventResponse::success('Event retrieved successfully.', $event);
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


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


}
