<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberAward
 * 
 * @property int $id
 * @property int|null $member_id
 * @property int|null $photo_id
 * @property string|null $award_standing
 * @property Carbon|null $award_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberAward extends Model
{
	use SoftDeletes;
	protected $table = 'member_awards';

	protected $casts = [
		'member_id' => 'int',
		'photo_id' => 'int',
		'award_date' => 'datetime'
	];

	protected $fillable = [
		'member_id',
		'photo_id',
		'award_standing',
		'award_date'
	];

	public function photo()
	{
		return $this->belongsTo(MemberPhoto::class, 'photo_id');
	}

}
