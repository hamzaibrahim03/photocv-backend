<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EventMember
 * 
 * @property int $id
 * @property int|null $event_id
 * @property int|null $member_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class EventMember extends Model
{
	use SoftDeletes;
	protected $table = 'event_members';

	protected $casts = [
		'event_id' => 'int',
		'member_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'member_id'
	];
}
