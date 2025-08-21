<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubCoverImage extends Model
{
    protected $fillable = ['club_setting_id', 'image_path'];

    protected $appends = ['url'];
    protected $hidden = ['id', 'club_setting_id', 'created_at', 'updated_at'];

    public function getUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function clubSetting()
    {
        return $this->belongsTo(ClubSetting::class);
    }
}
