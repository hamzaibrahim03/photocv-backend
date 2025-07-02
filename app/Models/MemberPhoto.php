<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberPhoto
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
class MemberPhoto extends Model
{
	use SoftDeletes;
	protected $table = 'member_photos';

	protected $casts = [
		'gallery_id' => 'int',
		'is_active' => 'bool',
		'allow_cc' => 'bool'
	];

	protected $fillable = [
		'gallery_id',
		'title',
		'image',
		'description',
		'is_active',
		'club_admin_notes',
		'allow_cc'
	];

	public function gallery()
	{
		return $this->belongsTo(MemberGallery::class, 'gallery_id');
	}

	public function likes()
	{
		return $this->hasMany(Comment::class, 'record_id')
			->where('record_type', 'photo')
			->where('comment_type', 'like');
	}

}
