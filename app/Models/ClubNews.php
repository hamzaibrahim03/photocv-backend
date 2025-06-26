<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Relationship: Page belongs to a catalog (page type).
     */
    public function clubNewsType()
    {
        return $this->belongsTo(Catalog::class, 'news_type_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'news')
            ->where('is_published', true)
            ->with('user'); // Eager load user
    }
}
