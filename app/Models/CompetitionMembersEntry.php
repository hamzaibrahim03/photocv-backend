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
	protected $appends = ['entry_image_url'];

	protected $casts = [
		'member_comp_id' => 'int'
	];

	protected $fillable = [
		'member_comp_id',
		'entry_type',
		'entry_image_title',
		'entry_image',
		'position',
		'total_score',
    	'is_published',

		// EXIF fields
		'camera_model',
		'lens',
		'focal_length',
		'aperture',
		'shutter_speed',
		'iso',
		'captured_at',

		// Fallback metadata
		'image_width',
		'image_height',
		'mime_type',
		'file_size',
		'color_type',
		'bit_depth',
	];

	public function competitionMember()
	{
		return $this->belongsTo(CompetitionMember::class, 'member_comp_id');
	}

	public function getEntryImageUrlAttribute()
	{
		if ($this->entry_image) {
			return url('storage/' . ltrim($this->entry_image, '/'));
		}
	}

	public function scores()
	{
		return $this->hasMany(CompetitionEntryScore::class, 'entry_id');
	}

	public function competition()
    {
        return $this->belongsTo(Competition::class, 'comp_id');
    }

}
