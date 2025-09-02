<?php
namespace App\Services\Member;

use App\Repositories\Member\BookingRepositoryInterface;
use App\Repositories\ClubAdmin\EventRepositoryInterface;
use App\Http\Resources\BookingResource;
use App\Http\Responses\MemberResponse;

class BookingService
{
    private $bookingRepository;
    private $eventRepository;

    public function __construct(BookingRepositoryInterface $bookingRepository, EventRepositoryInterface $eventRepository)
    {
        $this->bookingRepository = $bookingRepository;
        $this->eventRepository   = $eventRepository;
    }

    /**
     * Get all bookings of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        return $this->bookingRepository->index( $request );
    }

    /**
     * Method to store booking
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->bookingRepository->create($data);
    }

    /**
     * Method to show single booking
     * @param mixed $data
     */
    public function show($id)
    {
        $booking = $this->bookingRepository->find($id);

        if (!$booking) {
            return MemberResponse::error('Booking not found', 404);
        }

        return new BookingResource($booking);
    }

    /**
     * Method to update booking
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->bookingRepository->update($data, $id);
    }

    /**
     * Method to delete booking
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->bookingRepository->delete($id);
    }

    /**
     * Get booking extras.
     */
    public function bookingExtras()
    {
        $user = auth()->user();
        $clubIds = $user->clubs->pluck('id')->toArray();

        // Total events across all joined clubs
        $totalEvents = 0;
        foreach ($clubIds as $clubId) {
            $totalEvents += $this->eventRepository->getTotalEvents($clubId);
        }

        // Find the nearest upcoming event across all joined clubs
        $upcomingDays = null;
        foreach ($clubIds as $clubId) {
            $upcoming = $this->eventRepository->getUpcomingEventRemainingDays($clubId);

            if ($upcoming && isset($upcoming['remaining_days'])) {
                if ($upcomingDays === null || $upcoming['remaining_days'] < $upcomingDays) {
                    $upcomingDays = $upcoming['remaining_days'];
                }
            }
        }

        return [
            'event_stats' => [
                'total_events'   => $totalEvents,
                'upcoming_event' => $upcomingDays,
            ]
        ];
    }

}
