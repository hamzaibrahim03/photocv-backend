<?php

// app/Models/Booking.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'booking_type_id',
        'lead_source_id',
        'booking_status',
        'client_name',
        'email',
        'contact_number',
        'secondary_contact_number',
        'address',
        'event_date',
        'pre_event_meeting_date',
        'start_time',
        'end_time',
        'set_reminder',
        'event_reminders',
        'pre_event_reminder',
        'service_ids',
        'location',
        'deliverables',
        'special_requirements',
        'internal_notes',
        'client_notes',
        'contract_status',
        'requirements',
        'total_cost',
        'discount',
        'deposit_received',
        'remaining_balance',
        'payment_status',
        'payment_method',
        'payment_due_date',
        'invoice_number',
        'backup_gear_needed',
    ];

    protected $casts = [
        'service_ids' => 'array',
        'event_date' => 'date',
        'pre_event_meeting_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'set_reminder' => 'boolean',
        'event_reminders' => 'array',
        'pre_event_reminder' => 'boolean',
        'backup_gear_needed' => 'boolean',
        'total_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'deposit_received' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'payment_due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gears()
    {
        return $this->belongsToMany(Gear::class, 'booking_gear');
    }

    public function gearLibraries()
    {
        return $this->belongsToMany(GearLibrary::class, 'booking_gear_library')->withTimestamps();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function bookingType()
    {
        return $this->belongsTo(Catalog::class, 'booking_type_id');
    }

    public function leadSource()
    {
        return $this->belongsTo(Catalog::class, 'lead_source_id');
    }

    /**
     * Fetch services directly from catalog table based on service_ids array
     */
    public function services()
    {
        return Catalog::whereIn('id', $this->service_ids ?? [])
                    ->where('catalog_type', 'service_type')
                    ->get();
    }
}
