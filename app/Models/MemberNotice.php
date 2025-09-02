<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberNotice
 *
 * @property int $id
 * @property int|null $member_id
 * @property int|null $notice_type_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $tags
 * @property string|null $link_page_url
 * @property string|null $status
 * @property string|null $notice_image
 * @property bool|null $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberNotice extends Model
{
	use SoftDeletes;
	protected $table = 'member_notices';
	protected $appends = ['featured_image_url'];
	protected $hidden = ['featured_image'];

	protected $casts = [
		'member_id' => 'int',
		'notice_type_id' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'club_id',
		'featured_image',
		'member_id',
		'notice_type_id',
		'title',
		'description',
		'tags',
		'location',
		'link_page_url',
		'status',
		'poll',
		'urgency_importance',
		'comment_allowed',
		'notice_image',
		'notice_document',
		'is_active'
	];

	public function files()
	{
		return $this->hasMany(MemberNoticeFile::class, 'member_notice_id');
	}

	public function comments()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'notice')
            ->where('is_published', true)
            ->with('user');
    }

	public function likes()
	{
		return $this->hasMany(Comment::class, 'record_id')
			->where('record_type', 'notice')
			->where('comment_type', 'like')
			->where('is_published', true);
	}

	public function getFeaturedImageUrlAttribute()
	{
		if (!$this->featured_image) {
			return null;
		}

		// If already a full URL, return as is
		if (Str::startsWith($this->featured_image, ['http://', 'https://'])) {
			return $this->featured_image;
		}

		// Otherwise, prepend storage path
		return asset('storage/' . $this->featured_image);
	}

	public function club()
	{
		return $this->belongsTo(Club::class, 'club_id');
	}

	public function member()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function noticeType()
    {
        return $this->belongsTo(Catalog::class, 'notice_type_id', 'id');
    }
}
