<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'booking_type_id' => [
                'required',
                'integer',
                Rule::exists('catalog', 'id')->where(function ($query) {
                    $query->where('catalog_type', 'booking_type');
                }),
            ],

            'lead_source_id' => [
                'required',
                'integer',
                Rule::exists('catalog', 'id')->where(function ($query) {
                    $query->where('catalog_type', 'lead_source');
                }),
            ],

            'service_ids'   => 'nullable|array',
            'service_ids.*' => [
                'integer',
                Rule::exists('catalog', 'id')->where(function ($query) {
                    $query->where('catalog_type', 'service_type');
                }),
            ],
            'booking_status' => 'nullable|string|in:pending,confirmed,cancelled,completed',
            'client_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:20',
            'secondary_contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'pre_event_meeting_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'set_reminder' => 'boolean',
            'service_ids'   => 'nullable|array',
            'service_ids.*' => [
                'integer',
                Rule::exists('catalog', 'id')->where(function ($query) {
                    $query->where('catalog_type', 'service_type');
                }),
            ],
            'location' => 'nullable|string|max:255',
            'deliverables' => 'nullable|string',
            'special_requirements' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'client_notes' => 'nullable|string',
            'contract_status' => 'nullable|string|in:draft,signed,void',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'requirements' => 'nullable|string',
            'total_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'deposit_received' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|string|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string|max:100',
            'payment_due_date' => 'nullable|date',
            'invoice_number' => 'nullable|string|max:100',
            'backup_gear_needed' => 'boolean',
            'gear_ids' => 'nullable|array',
            'gear_ids.*' => 'integer|exists:gears,id',
            'gear_library_ids' => 'nullable|array',
            'gear_library_ids.*' => [
                'integer',
                Rule::exists('gear_libraries', 'id')->where('user_id', auth()->id()),
            ],
            'event_reminders' => 'nullable|array|max:5',
            'event_reminders.*' => 'integer|min:0|max:365', // days before event
            'pre_event_reminder' => 'boolean',
        ];

        if ($this->isMethod('POST')) {
            // For create, enforce stronger requirements
            $rules['title'] = 'required|string|max:255';
            $rules['client_name'] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A booking must have a title.',
            'client_name.required' => 'Client name is mandatory.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
        ];
    }
}
