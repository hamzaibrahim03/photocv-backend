<?php
namespace App\Services\Member;

use App\Repositories\Member\BookingRepositoryInterface;
use App\Http\Resources\BookingResource;
use App\Http\Responses\MemberResponse;

class BookingService
{
    private $bookingRepository;

    public function __construct(BookingRepositoryInterface $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index( $request )
    {
        return $this->bookingRepository->index( $request );
    }

    /**
     * Method to store note
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->bookingRepository->create($data);
    }

    /**
     * Method to show single note
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
     * Method to update note
     * @param mixed $data
     * @param mixed $id
     * @return void
     */
    public function update($data, $id)
    {
        return $this->bookingRepository->update($data, $id);
    }

    /**
     * Method to delete note
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->bookingRepository->delete($id);
    }
}
