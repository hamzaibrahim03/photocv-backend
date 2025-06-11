<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CompetitionMember
 * 
 * @property int $id
 * @property int|null $comp_id
 * @property int|null $member_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class CompetitionMember extends Model
{
	use SoftDeletes;
	protected $table = 'competition_members';

	protected $casts = [
		'comp_id' => 'int',
		'member_id' => 'int'
	];

	protected $fillable = [
		'comp_id',
		'member_id'
	];

	public function member()
	{
		return $this->belongsTo(Member::class);
	}

	public function competition()
	{
		return $this->belongsTo(Competition::class);
	}

}
