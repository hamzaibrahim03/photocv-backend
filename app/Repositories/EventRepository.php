<?php

namespace App\Repositories;

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

    public function show( $id )
    {
        try {
            $event = Event::findOrFail($id);
            if (!$event) {
                return EventResponse::error('Event not found.', 404);
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
            if (!$event) {
                return EventResponse::error('Event not found.', 404);
            }

            $event->update($data);
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

            if (!$event) {
                return EventResponse::error('Event not found or already deleted.', 404);
            }

            $event->delete();
            return EventResponse::success('Event deleted successfully.');
        } catch (\Exception $e) {
            return EventResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

}
