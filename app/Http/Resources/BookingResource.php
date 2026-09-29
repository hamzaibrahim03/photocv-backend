<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'booking_status' => $this->booking_status,
            'client_name' => $this->client_name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'secondary_contact_number' => $this->secondary_contact_number,
            'address' => $this->address,

            // Schedule
            'event_date' => $this->event_date?->format('Y-m-d'),
            'pre_event_meeting_date' => $this->pre_event_meeting_date?->format('Y-m-d'),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'set_reminder' => (bool)$this->set_reminder,
            'event_reminders' => $this->event_reminders ?? [],
            'pre_event_reminder' => (bool)$this->pre_event_reminder,

            // Booking Type & Lead Source
            'booking_type' => $this->bookingType ? [
                'id' => $this->bookingType->id,
                'title' => $this->bookingType->name,
            ] : null,

            'lead_source' => $this->leadSource ? [
                'id' => $this->leadSource->id,
                'title' => $this->leadSource->name,
            ] : null,

            // Services (from JSON array of catalog IDs)
            'services' => $this->services()->map(function ($service) {
                return [
                    'id' => $service->id,
                    'title' => $service->name,
                ];
            }),

            'location' => $this->location,
            'deliverables' => $this->deliverables,
            'special_requirements' => $this->special_requirements,
            'internal_notes' => $this->internal_notes,
            'client_notes' => $this->client_notes,
            'contract_status' => $this->contract_status,
            'requirements' => $this->requirements,

            // Payments
            'total_cost' => $this->total_cost,
            'discount' => $this->discount,
            'deposit_received' => $this->deposit_received,
            'remaining_balance' => $this->remaining_balance,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_due_date' => $this->payment_due_date?->format('Y-m-d'),
            'invoice_number' => $this->invoice_number,

            'backup_gear_needed' => (bool)$this->backup_gear_needed,

            // Gears (pivot)
            'gears' => $this->gears->map(function ($gear) {
                return [
                    'id' => $gear->id,
                    'title' => $gear->title,
                    'brand' => $gear->brand,
                    'model' => $gear->model,
                ];
            }),

            // Gear libraries / kits (pivot)
            'gear_libraries' => $this->whenLoaded('gearLibraries', function () {
                return $this->gearLibraries->map(function ($library) {
                    return [
                        'id' => $library->id,
                        'title' => $library->title,
                    ];
                });
            }, []),

            // Attachments (polymorphic)
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_type' => $attachment->file_type,
                    'file_path' => $attachment->file_path,
                    'url' => asset('storage/' . $attachment->file_path),
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
