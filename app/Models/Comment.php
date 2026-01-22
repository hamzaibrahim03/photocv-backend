<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Comment
 * 
 * @property int $id
 * @property int|null $event_id
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
class Comment extends Model
{
	use SoftDeletes;
	protected $table = 'comments';

	protected $casts = [
		'record_id' => 'int',
		'interacted_by' => 'int',
		'is_published' => 'bool'
	];

	protected $fillable = [
		'record_id',
		'record_type',
		'comment_type',
		'comment',
		'interacted_by',
		'is_published',
		'admin_notes',
		'is_viewed',
	];


	public function user()
	{
		return $this->belongsTo(User::class, 'interacted_by');
	}

	public function page()
	{
		return $this->belongsTo(Page::class, 'record_id');
	}

	public function clubNews()
	{
		return $this->belongsTo(ClubNews::class, 'record_id');
	}

	public function memberNotice()
	{
		return $this->belongsTo(MemberNotice::class, 'record_id');
	}

	public function event()
	{
		return $this->belongsTo(Event::class, 'record_id');
	}

	public function competitionEntry()
	{
		return $this->belongsTo(CompetitionMembersEntry::class, 'record_id');
	}

	public function photo()
	{
		return $this->belongsTo(Photo::class, 'record_id');
	}


}
