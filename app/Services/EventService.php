<?php
namespace App\Services;

use App\Repositories\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\DataTables;

class EventService
{
    private $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function allEvents( $request )
    {
        return $this->eventRepository->all( $request );
    }

    public function showEvent( $id ) {
        return $this->eventRepository->show( $id );
    }

    public function createEvent(array $data, $files)
    {
        return $this->eventRepository->create($data, $files);
    }

    public function updateEvent(int $id, array $data, $files)
    {
        return $this->eventRepository->update($id, $data, $files);
    }

    public function deleteEvent( $id )
    {
        return $this->eventRepository->delete($id);
    }
}
