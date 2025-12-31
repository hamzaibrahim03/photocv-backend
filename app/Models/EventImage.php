<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class EventImage extends Model
{
    use SoftDeletes;

    protected $table = 'event_images';

    protected $appends = [
        'image_url',
        'thumb_url',
        'medium_url',
        'large_url',
        'original_url',
    ];

    protected $fillable = [
        'event_id',
        'image',
        'created_by',
    ];

    protected $casts = [
        'event_id'   => 'int',
        'created_by'=> 'int',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /* ================= IMAGE ACCESSORS ================= */

    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('storage/' . ltrim($this->image, '/'))
            : null;
    }

    protected function resolveImage(string $size)
    {
        if (!$this->image) {
            return null;
        }

        $filename = basename($this->image);

        // ✅ FIXED PATH
        $path = $size === 'original'
            ? $this->image
            : "event_images/{$size}/{$filename}";

        return Storage::disk('public')->exists($path)
            ? URL::to('storage/' . $path)
            : null;
    }

    public function getThumbUrlAttribute()
    {
        return $this->resolveImage('thumb');
    }

    public function getMediumUrlAttribute()
    {
        return $this->resolveImage('medium');
    }

    public function getLargeUrlAttribute()
    {
        return $this->resolveImage('large');
    }

    public function getOriginalUrlAttribute()
    {
        return $this->resolveImage('original');
    }
}
