<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A member's request to buy a photo another member has listed for sale.
 *
 * @property int $id
 * @property int $photo_sale_id
 * @property int $requester_id
 * @property string $status pending|accepted|rejected
 */
class PhotoSaleRequest extends Model
{
    protected $table = 'photo_sale_requests';

    protected $fillable = [
        'photo_sale_id', 'requester_id', 'status',
    ];

    public function photoSale()
    {
        return $this->belongsTo(PhotoSale::class, 'photo_sale_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
