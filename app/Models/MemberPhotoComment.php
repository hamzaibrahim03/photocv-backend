<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberPhotoComment
 * 
 * @property int $id
 * @property int|null $member_id
 * @property int|null $photo_id
 * @property string|null $record_type
 * @property string|null $comment
 * @property int|null $interacted_by
 * @property bool|null $is_published
 * @property string|null $admin_notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberPhotoComment extends Model
{
	use SoftDeletes;
	protected $table = 'member_photo_comments';

	protected $casts = [
		'member_id' => 'int',
		'photo_id' => 'int',
		'interacted_by' => 'int',
		'is_published' => 'bool'
	];

	protected $fillable = [
		'member_id',
		'photo_id',
		'record_type',
		'comment',
		'interacted_by',
		'is_published',
		'admin_notes'
	];
}
