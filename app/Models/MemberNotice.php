<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberNotice
 *
 * @property int $id
 * @property int|null $member_id
 * @property int|null $notice_type_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $tags
 * @property string|null $link_page_url
 * @property string|null $status
 * @property bool|null $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 */
class MemberNotice extends Model
{
    use SoftDeletes;

    protected $table = 'member_notices';

    protected $appends = [
        'featured_image_url',
		'featured_image_thumb',
		'featured_image_medium',
		'featured_image_large',
		'featured_image_url',
    ];

    protected $hidden = [
        'featured_image',
    ];

    protected $casts = [
        'member_id'       => 'int',
        'notice_type_id'  => 'int',
        'is_active'       => 'bool',
    ];

    protected $fillable = [
        'club_id',
        'featured_image',
        'member_id',
        'notice_type_id',
        'title',
        'description',
        'tags',
        'location',
        'link_page_url',
        'status',
        'poll',
        'urgency_importance',
        'comment_allowed',
        'notice_image',
        'notice_document',
        'is_active',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function files()
    {
        return $this->hasMany(MemberNoticeFile::class, 'member_notice_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'notice')
            ->where('is_published', true)
            ->with('user');
    }

    public function likes()
    {
        return $this->hasMany(Comment::class, 'record_id')
            ->where('record_type', 'notice')
            ->where('comment_type', 'like')
            ->where('is_published', true);
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function noticeType()
    {
        return $this->belongsTo(Catalog::class, 'notice_type_id', 'id');
    }

    /* ================= ACCESSORS ================= */

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        if (Str::startsWith($this->featured_image, ['http://', 'https://'])) {
            return $this->featured_image;
        }

        return asset('storage/' . $this->featured_image);
    }

	public function getFeaturedImageThumbAttribute()
	{
		return $this->resolveFeaturedImage('thumb');
	}

	public function getFeaturedImageMediumAttribute()
	{
		return $this->resolveFeaturedImage('medium');
	}

	public function getFeaturedImageLargeAttribute()
	{
		return $this->resolveFeaturedImage('large');
	}

	protected function resolveFeaturedImage(string $size)
	{
		if (!$this->featured_image) {
			return null;
		}

		$filename = basename($this->featured_image);
		$path = "featured_images/{$size}/{$filename}";

		return \Storage::disk('public')->exists($path)
			? asset('storage/' . $path)
			: null;
	}

}
