<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A photo a member has listed for sale on their photographer dashboard.
 *
 * @property int $id
 * @property int $member_id
 * @property int|null $photo_id
 * @property string $title
 * @property string|null $description
 * @property float $price
 * @property string $currency
 * @property string $status
 * @property string|null $category
 * @property string|null $condition
 * @property string|null $image
 */
class PhotoSale extends Model
{
    protected $table = 'photo_sales';

    protected $fillable = [
        'member_id', 'photo_id', 'title', 'description', 'price', 'currency', 'status',
        'category', 'condition', 'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function photo()
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    public function requests()
    {
        return $this->hasMany(PhotoSaleRequest::class, 'photo_sale_id');
    }
}
