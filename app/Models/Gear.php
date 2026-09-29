<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gear extends Model
{
    use HasFactory;

    /**
     * Niche suitability columns shown as sliders on the gear form (0–100 scale).
     */
    public const SUITABILITY_FIELDS = [
        'suitability_wildlife', 'suitability_sports', 'suitability_portrait',
        'suitability_travel', 'suitability_street', 'suitability_wedding',
        'suitability_walk_around', 'suitability_vlogging', 'suitability_landscape',
        'suitability_macro', 'suitability_studio', 'suitability_event',
    ];

    protected $fillable = [
        'user_id',
        'title','brand','model','category_id','system','type','model_name','storage_name',
        'description','image',
        'purchase_date','purchase_price','vendor','serial_number',
        'ownership_type','warranty_expiry','insured','firmware_link',
        'sensor_type','focal_length','aperture','iso_range','weight','resolution',
        'mount','dimensions','min_focal_length','max_focal_length',
        'min_aperture','max_aperture','min_focus_distance','max_focus_distance','max_magnification',
        'suitability_wildlife','suitability_sports','suitability_portrait',
        'suitability_travel','suitability_street','suitability_wedding',
        'suitability_landscape','suitability_event',
        'suitability_walk_around','suitability_vlogging','suitability_macro','suitability_studio',
        'shutter_count','priority_tag','rating',
        'last_serviced_at','condition','is_available','notes',
        'is_for_sale','sale_price','sold_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_serviced_at' => 'date',
        'sold_at' => 'datetime',
        'is_available' => 'boolean',
        'insured' => 'boolean',
        'is_for_sale' => 'boolean',
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

    /**
     * Photos of the gear itself.
     */
    public function images()
    {
        return $this->hasMany(GearImage::class);
    }

    /**
     * Gallery photos taken using this gear.
     */
    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'gear_photo')->withTimestamps();
    }

    public function libraries()
    {
        return $this->belongsToMany(GearLibrary::class, 'gear_library_gear')->withTimestamps();
    }
}
