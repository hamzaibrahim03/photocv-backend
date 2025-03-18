<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Event
 * 
 * @property int $id
 * @property string|null $name
 * @property Carbon|null $event_date
 * @property string|null $description
 * @property int|null $event_type_id
 * @property int|null $event_kind_id
 * @property string|null $duration
 * @property string|null $speaker
 * @property string|null $speaker_club
 * @property string|null $speaker_qualification
 * @property string|null $status
 * @property string|null $required_gear
 * @property string|null $tags_keywords
 * @property string|null $url
 * @property string|null $rsvp_detail
 * @property bool|null $enable_dropbox_upload
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Event extends Model
{
	use SoftDeletes;
	protected $table = 'events';

	protected $casts = [
		'event_date' => 'datetime',
		'event_type_id' => 'int',
		'event_kind_id' => 'int',
		'enable_dropbox_upload' => 'bool',
		'created_by' => 'int',
		'updated_by' => 'int',
		'deleted_by' => 'int'
	];

	protected $fillable = [
		'name',
		'event_date',
		'description',
		'event_type_id',
		'event_kind_id',
		'duration',
		'speaker',
		'speaker_club',
		'speaker_qualification',
		'status',
		'required_gear',
		'tags_keywords',
		'url',
		'rsvp_detail',
		'enable_dropbox_upload',
		'created_by',
		'updated_by',
		'deleted_by'
	];

	public function images()
    {
        return $this->hasMany(EventImage::class, 'event_id');
    }
}
