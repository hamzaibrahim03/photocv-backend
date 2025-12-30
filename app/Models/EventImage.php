<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
	protected $appends = [
		'image_url',
		'thumb_url',
		'medium_url',
		'large_url',
		'original_url',
	];
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

	// Accessor to return full image URL
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('storage/' . ltrim($this->image, '/'));
        }
        return null;
    }

	public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

	public function getThumbUrlAttribute()
	{
		if (!$this->image) return null;

		$filename = basename($this->image);
		$path = "event_images/thumb/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

	public function getMediumUrlAttribute()
	{
		if (!$this->image) return null;

		$filename = basename($this->image);
		$path = "event_images/medium/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

	public function getLargeUrlAttribute()
	{
		if (!$this->image) return null;

		$filename = basename($this->image);
		$path = "event_images/large/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

	public function getOriginalUrlAttribute()
	{
		return $this->image && Storage::disk('public')->exists($this->image)
			? URL::to('storage/' . $this->image)
			: null;
	}
}
