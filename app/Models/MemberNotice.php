<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
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
            ->with('user'); // Eager load user
    }

	// Define relationship for likes (assuming is_like = true is stored)
	public function likes()
	{
		return $this->hasMany(Comment::class, 'record_id')
			->where('record_type', 'notice')
			->where('comment_type', 'like') // or use is_like = true if stored as boolean
			->where('is_published', true);
	}

	public function getFeaturedImageUrlAttribute()
	{
		return $this->featured_image
			? asset('storage/' . $this->featured_image)
			: null;
	}
}
