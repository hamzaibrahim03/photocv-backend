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

	protected $appends = [
		'about_img_url',
		'footer_img_url',
		'logo_url',
		'cover_image_url',
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
		'typography',
		'contact_details',
		'domain_type',
		'domain_name',
		'time_zone',
		'font',
		'background_color',
		'header_text',
		'about_title',
		'about_description',
		'about_img',
		'footer_img',
		'footer_text',
		'logo',
		'cover_image',
		'registration',
		'directory_visibility',
		'comments',
		'likes',
		'news',
		'website_sections',
		'comment_preference',
		'events',
		'galleries',
		'competitions',
		'home_page_blocks',
		'reminders',
		'fb_link',
		'insta_link',
		'flickr_link',
		'gdpr_privacy_policy_management',
		'cookies',
		'cookies_description',
		'data_collection_preference',
		'data_collection_preferences_description',
		'allow_reporting',
		'allow_reporting_description',
		'created_by',
		'updated_by',
		'deleted_by'
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

	public function getAboutImgUrlAttribute()
	{
		return $this->about_img ? asset('storage/' . $this->about_img) : null;
	}

	public function getFooterImgUrlAttribute()
	{
		return $this->footer_img ? asset('storage/' . $this->footer_img) : null;
	}

	public function getLogoUrlAttribute()
	{
		return $this->logo ? asset('storage/' . $this->logo) : null;
	}

	public function getCoverImageUrlAttribute()
	{
		return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
	}
}
