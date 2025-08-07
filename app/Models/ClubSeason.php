<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubSeason extends Model
{
    protected $hidden = ['id', 'club_id', 'created_at', 'updated_at'];

    protected $fillable = [
        'club_id',
        'name',
        'status',
        'start_date',
        'end_date',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
