<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberWebsiteSetting extends Model
{
    protected $fillable = [
        'member_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
