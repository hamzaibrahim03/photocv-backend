<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GearWishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'list_type',
        'title',
        'category_id',
        'price',
        'image',
        'link',
        'is_purchased',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_purchased' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Catalog::class, 'category_id');
    }
}
