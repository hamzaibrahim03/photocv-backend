<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberClub
 * 
 * @property int $id
 * @property int|null $member_id
 * @property int|null $club_id
 * @property Carbon|null $joining_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberClub extends Model
{
	use SoftDeletes;
	protected $table = 'member_clubs';

	protected $casts = [
		'member_id' => 'int',
		'club_id' => 'int',
		'joining_date' => 'datetime'
	];

	protected $fillable = [
		'member_id',
		'club_id',
		'joining_date'
	];
}
