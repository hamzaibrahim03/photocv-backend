<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedLibraryItem extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'item_id',
        'title',
        'description',
        'date',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
