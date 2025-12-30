<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ClubNews extends Model
{
    use HasFactory;

    protected $table = 'club_news';

    protected $fillable = [
        'club_id',
        'title',
        'news_type_id',
        'short_description',
        'description',
        'featured_image',
        'publish_date',
    ];

    protected $hidden = ['featured_image'];

    protected $appends = [
        'featured_image_url',
        'featured_image_thumb',
        'featured_image_medium',
        'featured_image_large',
    ];

    /* ================= RELATIONS ================= */

    public function clubNewsType()
    {
        return $this->belongsTo(Catalog::class, 'news_type_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'news')
            ->where('is_published', true)
            ->with('user');
    }

    /* ================= ACCESSORS ================= */

    public function getFeaturedImageUrlAttribute()
    {
        return $this->resolveFeaturedImage('original');
    }

    public function getFeaturedImageThumbAttribute()
    {
        return $this->resolveFeaturedImage('thumb');
    }

    public function getFeaturedImageMediumAttribute()
    {
        return $this->resolveFeaturedImage('medium');
    }

    public function getFeaturedImageLargeAttribute()
    {
        return $this->resolveFeaturedImage('large');
    }

    protected function resolveFeaturedImage(string $size): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        $filename = basename($this->featured_image);
        $path = "news/featured/{$size}/{$filename}";

        return Storage::disk('public')->exists($path)
            ? asset('storage/' . $path)
            : null;
    }
}
