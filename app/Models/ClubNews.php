<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubNews extends Model
{
    use HasFactory;

    protected $table = 'club_news';

    protected $fillable = [
        'title',
        'news_type_id',
        'short_description',
        'description',
        'thumb_image',
        'publish_date',
    ];

    /**
     * Relationship: Page belongs to a catalog (page type).
     */
    public function clubNewsType()
    {
        return $this->belongsTo(Catalog::class, 'news_type_id');
    }
}
