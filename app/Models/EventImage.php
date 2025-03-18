<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EventImage
 * 
 * @property int|null $id
 * @property int|null $event_id
 * @property string|null $image
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class EventImage extends Model
{
	use SoftDeletes;
	protected $table = 'event_images';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'event_id' => 'int',
		'created_by' => 'int'
	];

	protected $fillable = [
		'id',
		'event_id',
		'image',
		'created_by'
	];

	public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
