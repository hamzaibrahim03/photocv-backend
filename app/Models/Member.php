<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Member
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $header_image
 * @property string|null $club_privacy
 * @property string|null $header_text
 * @property string|null $footer_text
 * @property string|null $background_color
 * @property string|null $font
 * @property string|null $logo
 * @property string|null $cover_image
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
class Member extends Model
{
	use SoftDeletes;
	protected $table = 'members';

	protected $casts = [
		'user_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'deleted_by' => 'int',
		'color_theme' => 'array',
		'social_links_visibility' => 'array',
	];

	protected $fillable = [
		'user_id',
        'club_privacy',
        'profile_privacy',
        'header_text',
        'footer_text',
        'domain_name',
        'domain_type',
        'color_theme',
        'font',
        'logo',
        'header_image',
        'social_links_visibility',
        'user_consent_cookie',
        'data_collection_preference',
        'content_moderation_reporting',
        'created_by',
        'updated_by',
        'deleted_by',
	];

	public function memberContact()
	{
		return $this->hasOne(MemberContact::class);
	}

	public function memberBrand()
	{
		return $this->hasOne(MemberBrand::class);
	}

	public function memberSocialLinks()
	{
		return $this->hasMany(MemberSocialLink::class);
	}

	public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coverImages()
    {
        return $this->hasMany(MemberCoverImage::class);
    }

	public function socialLinks()
	{
		return $this->hasMany(MemberSocialLink::class, 'member_id', 'user_id');
	}


}
