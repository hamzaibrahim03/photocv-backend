<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;

class EventController extends Controller
{
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index( Request $request )
    {
        return $this->eventService->allEvents( $request );
    }

    public function show( $id ) {
        return $this->eventService->showEvent( $id );
    }

    public function store(StoreEventRequest $request)
    {
        return $this->eventService->createEvent($request->except('images'), $request->file('images'));
    }

    public function update(UpdateEventRequest $request, $id)
    {
        return $this->eventService->updateEvent( $id, $request->except('images'), $request->file('images') );
    }

    public function destroy( $id )
    {
        return $this->eventService->deleteEvent( $id );
    }
}
