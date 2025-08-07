<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Class Club
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $contact_details
 * @property string|null $domain_type
 * @property string|null $domain_name
 * @property string|null $time_zone
 * @property string|null $font
 * @property string|null $background_color
 * @property string|null $header_text
 * @property string|null $footer_text
 * @property string|null $logo
 * @property string|null $cover_image
 * @property string|null $registration
 * @property string|null $directory_visibility
 * @property string|null $comments
 * @property string|null $likes
 * @property string|null $news
 * @property string|null $events
 * @property string|null $galleries
 * @property string|null $competitions
 * @property string|null $home_page_blocks
 * @property string|null $reminders
 * @property string|null $fb_link
 * @property string|null $insta_link
 * @property string|null $flickr_link
 * @property string|null $privacy_policy
 * @property string|null $user_consent_cookie
 * @property string|null $data_collection_preference
 * @property string|null $content_moderation_reporting
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Club extends Model
{
	use SoftDeletes;
	protected $table = 'clubs';

	protected $hidden = [
		'id',
		'user_id',
		'created_at',
		'updated_at',
		'created_by',
		'updated_by',
		'deleted_by',
		'deleted_at'
	];

	protected $casts = [
		'user_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'deleted_by' => 'int'
	];

	protected $fillable = [
		'user_id',
		'club_name',
		'tag_line',
		'about',
		'contact_details',
		'domain_type',
		'domain_name',
		'created_by',
		'updated_by',
		'deleted_by',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function events()
	{
		return $this->hasMany(Event::class);
	}

	public function users()
	{
		return $this->belongsToMany(User::class)->withTimestamps()->withPivot('joined_at');
	}

	public function getRelatedEntityAttribute()
	{
		if (!is_null($this->club_id)) {
			return $this->club;
		}

		if (!is_null($this->member_id)) {
			return $this->member;
		}

		return null;
	}

	public function setting()
	{
		return $this->hasOne(ClubSetting::class);
	}

	public function seasons()
	{
		return $this->hasMany(ClubSeason::class);
	}
}
