<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

/**
 * Class Photo
 *
 * @property int $id
 * @property int|null $gallery_id
 * @property string|null $title
 * @property string|null $image
 * @property string|null $description
 * @property bool|null $is_active
 * @property string|null $club_admin_notes
 * @property bool|null $allow_cc
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Photo extends Model
{
	use SoftDeletes;
	protected $table = 'photos';

	protected $appends = [
		'image_url',
		'thumb_url',
        'medium_url',
        'large_url',
        'original_url',
	];

	protected $hidden = ['image'];

	protected $casts = [
		'gallery_id' => 'int',
		'is_active' => 'bool',
		'allow_cc' => 'bool',
		'allow_comments' => 'bool',
		'allow_likes' => 'bool',
		'show_in_portfolio' => 'bool',
		'show_exif' => 'bool',
		'image_sizes' => 'array',
	];

	protected $fillable = [
		'gallery_id', 'title', 'image', 'description', 'is_active',
		'club_admin_notes', 'allow_cc', 'uploaded_by',

		// Image settings
		'allow_comments', 'allow_likes', 'visibility',
		'show_in_portfolio', 'show_exif',

		// EXIF fields
		'camera_model', 'lens', 'focal_length', 'aperture',
		'shutter_speed', 'iso', 'captured_at',

		// Fallback metadata
		'image_width', 'image_height', 'mime_type',
		'file_size', 'color_type', 'bit_depth'
	];

	public function gallery()
	{
		return $this->belongsTo(Gallery::class, 'gallery_id');
	}

	public function likes()
	{
		return $this->hasMany(Comment::class, 'record_id')
			->where('record_type', 'photo')
			->where('comment_type', 'liking');
	}

	public function getImageUrlAttribute()
	{
		return $this->image
			? URL::to('storage/' . $this->image)
			: null;
	}

	public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

	public function comments()
	{
		return $this->hasMany(Comment::class, 'record_id')
			->where('record_type', 'photo')
			->where('comment_type', 'comment')
			->whereNotNull('comment'); // Ensure it's a real comment
	}

	public function commentsOrLikes()
	{
		return $this->hasMany(Comment::class, 'record_id')->where('record_type', 'photo');
	}

	public function getThumbUrlAttribute()
	{
		if (!$this->image) {
			return null;
		}

		$filename = basename($this->image);
		$path = "member-galleries/thumb/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

	public function getMediumUrlAttribute()
	{
		if (!$this->image) {
			return null;
		}

		$filename = basename($this->image);
		$path = "member-galleries/medium/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

	public function getLargeUrlAttribute()
	{
		if (!$this->image) {
			return null;
		}

		$filename = basename($this->image);
		$path = "member-galleries/large/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

	public function getOriginalUrlAttribute()
	{
		if (!$this->image) {
			return null;
		}

		return Storage::disk('public')->exists($this->image)
			? URL::to('storage/' . $this->image)
			: null;
	}

}
