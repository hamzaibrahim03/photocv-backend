<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberGallery
 * 
 * @property int $id
 * @property int|null $member_id
 * @property int|null $club_id
 * @property string|null $gallery_name
 * @property bool|null $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberGallery extends Model
{
	use SoftDeletes;
	protected $table = 'member_galleries';

	protected $casts = [
		'member_id' => 'int',
		'club_id' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'member_id',
		'club_id',
		'gallery_name',
		'is_active'
	];
}
