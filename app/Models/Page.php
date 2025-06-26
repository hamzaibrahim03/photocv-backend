<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';

    protected $fillable = [
        'club_id',
        'title',
        'publish_date',
        'description',
        'featured_image',
        'page_type_id',
        'page_slug',
        'status',
    ];

    /**
     * Relationship: Page belongs to a catalog (page type).
     */
    public function pageType()
    {
        return $this->belongsTo(Catalog::class, 'page_type_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'page')
            ->where('is_published', true)
            ->with('user'); // Eager load user
    }
}
