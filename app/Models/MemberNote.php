<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberNote extends Model
{
    protected $fillable = [
        'member_id', 'title', 'description', 'type',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
