<?php

namespace App\Repositories\Member;

use App\Http\Responses\MemberResponse;
use App\Traits\DataTables\MemberDataTableTrait;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookingRepository implements BookingRepositoryInterface
{
    use MemberDataTableTrait;

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function index( $request )
    {
        try {
            $memberNotes = $this->getMemberBookings($request, auth()->user()->id);
            return MemberResponse::success('MemberNotes retrieved successfully.', $memberNotes);
        } catch (\Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to store booking
     * @param mixed $data
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function create($data)
    {
        try {
            DB::beginTransaction();

            $data['user_id'] = auth()->id();
            $booking = Booking::create($data);

            if (isset($data['gear_ids'])) {
                $booking->gears()->sync($data['gear_ids']);
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    // Store in storage/app/public/attachments
                    $path = $file->store('attachments', 'public');

                    $booking->attachments()->create([
                        'file_path' => $path,
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            DB::commit();

            return MemberResponse::success('Booking created successfully.', $booking, 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to show single booking information
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function find($id)
    {
        return Booking::with(['gears', 'attachments', 'bookingType', 'leadSource'])
                  ->find($id);
    }

    /**
     * Method to update note
     * @param mixed $data
     * @param mixed $id
     */
    public function update($data, $id)
    {
        try {
            DB::beginTransaction();

            // Find the booking
            $booking = Booking::findOrFail($id);

            // Update main booking data
            $booking->update($data);

            // Update gears if provided
            if (isset($data['gear_ids'])) {
                $booking->gears()->sync($data['gear_ids']);
            }

            // Update attachments if provided
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    // Store file in storage
                    $path = $file->store('attachments', 'public');

                    $booking->attachments()->create([
                        'file_path' => $path,
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // Update service_ids JSON
            if (isset($data['service_ids'])) {
                $booking->service_ids = $data['service_ids'];
                $booking->save();
            }

            DB::commit();

            return MemberResponse::success('Booking updated successfully.', $booking);

        } catch (\Exception $e) {
            DB::rollBack();
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to delete note
     * @param mixed $id
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            // Find the booking
            $booking = Booking::findOrFail($id);

            // Detach gears (pivot table)
            $booking->gears()->detach();

            // Delete attachments and remove files from storage
            foreach ($booking->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            // Delete the booking itself
            $booking->delete();

            DB::commit();

            return MemberResponse::success('Booking deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
