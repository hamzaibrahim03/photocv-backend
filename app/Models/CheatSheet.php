<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheatSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'reference_image',
        'shutter_min',
        'shutter_max',
        'iso_min',
        'iso_max',
        'aperture_min',
        'aperture_max',
        'lens',
        'camera',
        'white_balance',
        'noise_reduction',
        'notes',
        'accessories',
        'tags',
        'visibility',
    ];

    protected $casts = [
        'accessories' => 'array',
        'tags' => 'array',
        'iso_min' => 'integer',
        'iso_max' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Catalog::class, 'category_id');
    }
}
