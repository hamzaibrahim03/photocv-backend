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
}
