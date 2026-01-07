<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CompetitionMembersEntry extends Model
{
    use SoftDeletes;

    protected $table = 'competition_members_entries';

    protected $appends = [
		'entry_image_url',
        'entry_image_original_url',
        'entry_image_thumb',
        'entry_image_medium',
        'entry_image_large',
    ];

    protected $hidden = [
        'entry_image',
    ];

    protected $casts = [
        'member_comp_id' => 'int',
        'is_published'   => 'bool',
    ];

    protected $fillable = [
        'member_comp_id',
        'entry_type',
        'entry_image_title',
        'entry_image',
        'position',
        'total_score',
        'is_published',

        // EXIF
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

    /* ================= RELATIONS ================= */

    public function competitionMember()
    {
        return $this->belongsTo(
            CompetitionMember::class,
            'member_comp_id'
        );
    }

    public function scores()
    {
        return $this->hasMany(
            CompetitionEntryScore::class,
            'entry_id'
        );
    }

    /* ================= ACCESSORS ================= */

	public function getEntryImageUrlAttribute()
	{
		if ($this->entry_image) {
			return url('storage/' . ltrim($this->entry_image, '/'));
		}
	}

    public function getEntryImageOriginalUrlAttribute()
    {
        return $this->resolveImage('original');
    }

    public function getEntryImageThumbAttribute()
    {
        return $this->resolveImage('thumb');
    }

    public function getEntryImageMediumAttribute()
    {
        return $this->resolveImage('medium');
    }

    public function getEntryImageLargeAttribute()
    {
        return $this->resolveImage('large');
    }

    protected function resolveImage(string $size): ?string
    {
        if (!$this->entry_image) {
            return null;
        }

        $filename = basename($this->entry_image);
        $path = "competition_entries/{$size}/{$filename}";

        return Storage::disk('public')->exists($path)
            ? asset('storage/' . $path)
            : null;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'competition_entry')
            ->where('comment_type', 'comment')
            ->latest();
    }

    // optional (if you also want likes)
    public function likes()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'competition_entry')
            ->where('comment_type', 'liking');
    }

}
