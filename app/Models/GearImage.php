<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GearImage extends Model
{
    protected $fillable = [
        'gear_id',
        'image_path',
    ];

    protected $appends = ['image_url'];

    public function gear()
    {
        return $this->belongsTo(Gear::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
