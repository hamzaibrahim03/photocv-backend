<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GearLibrary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gears()
    {
        return $this->belongsToMany(Gear::class, 'gear_library_gear')->withTimestamps();
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_gear_library')->withTimestamps();
    }
}
