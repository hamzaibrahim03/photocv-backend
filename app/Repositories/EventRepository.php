<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\EventImage;
use App\Traits\UtilityTrait;

class EventRepository implements EventRepositoryInterface
{
    use UtilityTrait;

    public function all( $request )
    {
        return $this->getAllEventData($request);
    }

    public function show( $id )
    {
        $event = Event::findOrFail($id);
        $event->load('images');

        return $event;
    }

    public function create(array $data, $files = null)
    {
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

        return $event;
    }

    public function update($id, array $data, $files = null)
    {
        $event = Event::findOrFail($id);

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

        return $event;
    }

    public function delete($id)
    {
        $event = Event::findOrFail($id);
        if ( $event ) {
            $event->delete();
            return 'Event Deleted Succesfully';
        }
        return $event;
    }

}
