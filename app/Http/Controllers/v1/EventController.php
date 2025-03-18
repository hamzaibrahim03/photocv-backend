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

    public function index()
    {
        return response()->json($this->eventService->allEvents( ));
    }

    public function show( $id ) {
        return response()->json( $this->eventService->showEvent( $id ) );
    }

    public function store(StoreEventRequest $request)
    {
        try {
            return response()->json(
                $this->eventService->createEvent($request->except('images'), $request->file('images'))
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateEventRequest $request, $id)
    {
        try {
            return response()->json( $this->eventService->updateEvent( $id, $request->except('images'), $request->file('images') ) );
        } catch ( \Exception $e ) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy( $id )
    {
        return response()->json($this->eventService->deleteEvent( $id ) );
    }
}
