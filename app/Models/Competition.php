<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Class Competition
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $competition_type_id
 * @property int|null $judging_type_id
 * @property Carbon|null $start_date
 * @property Carbon|null $submission_deadline
 * @property int|null $max_entries_print
 * @property int|null $max_entries_digital
 * @property string|null $allowed_image_formats
 * @property int|null $max_file_size
 * @property string|null $status
 * @property int|null $category_id
 * @property int|null $theme_id
 * @property string|null $print_vs_digital
 * @property string|null $color_vs_mono
 * @property string|null $judging_panel
 * @property int|null $voting_method_id
 * @property int|null $result_method_id
 * @property Carbon|null $result_announcement_date
 * @property string|null $top_places
 * @property int|null $high_commendation_number
 * @property int|null $commendation_number
 * @property string|null $prizes
 * @property bool|null $cc_allowed
 * @property bool|null $auto_certificate
 * @property bool|null $alllow_judges_feedback
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Competition extends Model
{
	use SoftDeletes;
	protected $table = 'competitions';
	protected $appends = [
		'featured_image_url',
		'featured_thumb_url',
		'featured_medium_url',
		'featured_large_url',
		'featured_original_url',
	];
	// protected $hidden = ['featured_image'];

	/**
	 * Always load the owning club so every competition API can return it.
	 */
	protected $with = ['club.setting'];

	protected $casts = [
		'competition_type_id' => 'int',
		'judging_type_id' => 'int',
		'start_date' => 'datetime',
		'submission_deadline' => 'datetime',
		'judging_start_date' => 'datetime',
		'judging_end_date' => 'datetime',
		'max_entries_print' => 'int',
		'max_entries_digital' => 'int',
		'max_file_size' => 'int',
		'category_id' => 'int',
		'theme_id' => 'int',
		'voting_method_id' => 'int',
		'result_method_id' => 'int',
		'result_announcement_date' => 'datetime',
		'high_commendation_number' => 'int',
		'commendation_number' => 'int',
		'cc_allowed' => 'bool',
		'auto_certificate' => 'bool',
		'allow_judges_feedback' => 'bool',
		'created_by' => 'int',
		'updated_by' => 'int',
		'deleted_by' => 'int'
	];

	protected $fillable = [
		'club_id',
		'featured_image',
		'name',
		'description',
		'competition_type_id',
		'judging_type_id',
		'start_date',
		'submission_deadline',
		'judging_start_date',
		'judging_end_date',
		'max_entries_print',
		'max_entries_digital',
		'allowed_image_formats',
		'max_file_size',
		'status',
		'category_id',
		'theme_id',
		'print_vs_digital',
		'color_vs_mono',
		'judging_panel',
		'voting_method_id',
		'result_method_id',
		'result_announcement_date',
		'top_places',
		'high_commendation_number',
		'commendation_number',
		'prizes',
		'cc_allowed',
		'auto_certificate',
		'allow_judges_feedback',
		'photographer_name',
        'comments',
        'scores',
        'position',
        'reels',
        'arrows',
		'comments_and_critique',
		'auto_generate_certificates',
		'visible_judges_feedback',
		'created_by',
		'updated_by',
		'deleted_by'
	];

	public function competitionMembers()
	{
		return $this->hasMany(CompetitionMember::class, 'comp_id');
	}

	public function getFeaturedImageUrlAttribute()
	{
		return $this->featured_image
			? asset('storage/' . $this->featured_image)
			: null;
	}

	public function competitionType()
    {
        return $this->belongsTo(Catalog::class, 'competition_type_id', 'id');
    }

    public function judgingType()
    {
        return $this->belongsTo(Catalog::class, 'judging_type_id', 'id');
    }

	public function resultMethod()
    {
        return $this->belongsTo(Catalog::class, 'result_method_id', 'id');
    }

	public function votingMethod()
    {
        return $this->belongsTo(Catalog::class, 'voting_method_id', 'id');
    }

	public function competitionTheme()
    {
        return $this->belongsTo(Catalog::class, 'theme_id', 'id');
    }

	public function competitionCategory()
    {
        return $this->belongsTo(Catalog::class, 'category_id', 'id');
    }

	public function judges()
	{
		return $this->belongsToMany(User::class, 'competition_judges')
					->using(CompetitionJudge::class)
					->withTimestamps();
	}

	public function club()
	{
		return $this->belongsTo(Club::class, 'club_id');
	}

	/**
	 * Serialize the club relation as its compact summary (id, name, tag line,
	 * domain, logo) rather than the full model, whose id is hidden.
	 */
	public function relationsToArray()
	{
		$relations = parent::relationsToArray();

		if ($this->relationLoaded('club')) {
			$relations['club'] = $this->club?->summary();
		}

		return $relations;
	}

	public function getFeaturedThumbUrlAttribute()
	{
		return $this->getFeaturedImageBySize('thumb');
	}

	public function getFeaturedMediumUrlAttribute()
	{
		return $this->getFeaturedImageBySize('medium');
	}

	public function getFeaturedLargeUrlAttribute()
	{
		return $this->getFeaturedImageBySize('large');
	}

	public function getFeaturedOriginalUrlAttribute()
	{
		return $this->getFeaturedImageBySize('original');
	}

	protected function getFeaturedImageBySize(string $size)
	{
		if (!$this->featured_image) {
			return null;
		}

		$filename = basename($this->featured_image);

		$path = $size === 'original'
			? $this->featured_image
			: "featured_images/{$size}/{$filename}";

		return Storage::disk('public')->exists($path)
			? URL::to('storage/' . $path)
			: null;
	}

}
