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
        'thumb_image',
        'page_type_id',
        'page_slug',
    ];

    /**
     * Relationship: Page belongs to a catalog (page type).
     */
    public function pageType()
    {
        return $this->belongsTo(Catalog::class, 'page_type_id');
    }
}
