<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberClassLog extends Model
{
    protected $fillable = [
        'member_id',
        'title',
        'description',
        'image',
        'start_date',
        'end_date',
        'present',
    ];

    protected $appends = ['image_url'];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url($this->image) : null;
    }
}
