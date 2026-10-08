<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ClubConfigItem extends Model
{
    protected $fillable = [
        'club_id',
        'group',
        'name',
        'icon',
    ];

    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        if (!$this->icon) {
            return null;
        }

        return Storage::disk('public')->exists($this->icon)
            ? URL::to('storage/' . $this->icon)
            : null;
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
