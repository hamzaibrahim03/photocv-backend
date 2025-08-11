<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\EventRepositoryInterface;

class EventService
{
    private $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Get all events with pagination and filtering.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allEvents( $request )
    {
        return $this->eventRepository->all( $request );
    }

    /**
     * Show a specific event by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEvent( $id ) {
        return $this->eventRepository->show( $id );
    }

    /**
     * Create a new event.
     *
     * @param array $data
     * @param mixed $files
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEvent(array $data, $files)
    {
        return $this->eventRepository->create($data, $files);
    }

    /**
     * Update an existing event.
     *
     * @param int $id
     * @param array $data
     * @param mixed $files
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEvent(int $id, array $data, $files)
    {
        return $this->eventRepository->update($id, $data, $files);
    }

    /**
     * Delete an event by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEvent( $id )
    {
        return $this->eventRepository->delete($id);
    }

    /**
     * Get event extras.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventExtras( $request )
    {
        return $this->eventRepository->getEventExtras( $request );
    }
}
