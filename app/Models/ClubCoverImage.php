<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ClubCoverImage extends Model
{
    protected $fillable = ['club_setting_id', 'image_path'];

    protected $appends = [
        'url',
        'image_url',
        'image_thumb_url',
        'image_medium_url',
        'image_large_url',
    ];
    protected $hidden = ['id', 'club_setting_id', 'created_at', 'updated_at'];
    

    public function getUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function clubSetting()
    {
        return $this->belongsTo(ClubSetting::class);
    }

    /**
     * Helper method to get image URL for specific size
     */
    private function getSizeUrl($size = 'original')
    {
        if (!$this->image_path) {
            return null;
        }

        $filename = basename($this->image_path);
        $baseFolder = 'uploads/clubs/cover';
        
        // For original size, use the stored path
        if ($size === 'original') {
            $sizePath = $this->image_path;
        } else {
            $sizePath = $baseFolder . '/' . $size . '/' . $filename;
        }
        
        return Storage::disk('public')->exists($sizePath)
            ? URL::to('storage/' . $sizePath)
            : null;
    }

    public function getImageUrlAttribute()
    {
        return $this->getSizeUrl();
    }
    
    public function getImageThumbUrlAttribute()
    {
        return $this->getSizeUrl('thumb');
    }
    
    public function getImageMediumUrlAttribute()
    {
        return $this->getSizeUrl('medium');
    }
    
    public function getImageLargeUrlAttribute()
    {
        return $this->getSizeUrl('large');
    }
}
