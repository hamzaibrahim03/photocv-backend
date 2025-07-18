<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPracticeLog extends Model
{
    protected $fillable = [
        'member_id', 'title', 'description', 'file', 'type',
    ];

    protected $appends = ['file_url'];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
    ];

    public function getFileUrlAttribute()
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
