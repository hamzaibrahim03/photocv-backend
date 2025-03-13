<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberSocialLink
 * 
 * @property int $id
 * @property int|null $member_id
 * @property string|null $social_media_name
 * @property string|null $social_link
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberSocialLink extends Model
{
	use SoftDeletes;
	protected $table = 'member_social_links';

	protected $casts = [
		'member_id' => 'int'
	];

	protected $fillable = [
		'member_id',
		'social_media_name',
		'social_link'
	];
}
