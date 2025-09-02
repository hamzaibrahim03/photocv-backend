<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannedLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'feature_image',
        'location',
        'description',
        'visited',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
