<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClubAdmin\EventService;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;

class EventController extends Controller
{
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index( Request $request )
    {
        return $this->eventService->allEvents( $request );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show( $id ) {
        return $this->eventService->showEvent( $id );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Event\StoreEventRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request)
    {
        return $this->eventService->createEvent($request->except('images'), $request->file('images'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Event\UpdateEventRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, $id)
    {
        return $this->eventService->updateEvent( $id, $request->except('images'), $request->file('images') );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy( $id )
    {
        return $this->eventService->deleteEvent( $id );
    }

    /**
     * Get event extras.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventExtras(Request $request)
    {
        return $this->eventService->getEventExtras( $request );
    }
}
