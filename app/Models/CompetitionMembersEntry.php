<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CompetitionMembersEntry
 * 
 * @property int $id
 * @property int|null $member_comp_id
 * @property string|null $entry_type
 * @property string|null $entry_image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class CompetitionMembersEntry extends Model
{
	use SoftDeletes;
	protected $table = 'competition_members_entries';

	protected $casts = [
		'member_comp_id' => 'int'
	];

	protected $fillable = [
		'member_comp_id',
		'entry_type',
		'entry_image'
	];

	public function competitionMember()
	{
		return $this->belongsTo(CompetitionMember::class, 'member_comp_id');
	}

}
