<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gear extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','brand','model','category_id','description','image',
        'purchase_date','purchase_price','vendor','serial_number',
        'ownership_type','warranty_expiry',
        'sensor_type','focal_length','aperture','iso_range','weight','resolution',
        'suitability_wildlife','suitability_sports','suitability_portrait',
        'suitability_travel','suitability_street','suitability_wedding',
        'suitability_landscape','suitability_event',
        'last_serviced_at','condition','is_available','notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_serviced_at' => 'date',
        'is_available' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_gear');
    }

    public function category()
    {
        return $this->belongsTo(Catalog::class, 'category_id');
    }
}
