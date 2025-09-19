<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannedDayOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'planned_date',
        'photography_description',
        'location',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
