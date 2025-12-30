<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MemberNoticeFile extends Model
{
    use HasFactory;

    protected $table = 'member_notice_files';

    protected $fillable = [
        'member_notice_id',
        'file_name',
        'file_type',   // image | document
        'file_path',
    ];

    protected $appends = [
        'thumb_url',
        'medium_url',
        'large_url',
        'original_url',
    ];

    protected $hidden = [
        'file_path',
    ];

    /* ================= RELATIONSHIP ================= */

    public function notice()
    {
        return $this->belongsTo(MemberNotice::class, 'member_notice_id');
    }

    /* ================= ACCESSORS ================= */

    public function getThumbUrlAttribute(): ?string
    {
        return $this->file_type === 'image'
            ? $this->resolveImage('thumb')
            : null;
    }

    public function getMediumUrlAttribute(): ?string
    {
        return $this->file_type === 'image'
            ? $this->resolveImage('medium')
            : null;
    }

    public function getLargeUrlAttribute(): ?string
    {
        return $this->file_type === 'image'
            ? $this->resolveImage('large')
            : null;
    }

    public function getOriginalUrlAttribute(): ?string
    {
        return $this->file_type === 'image'
            ? $this->resolveImage('original')
            : null;
    }

    /* ================= HELPERS ================= */

    protected function resolveImage(string $size): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        $filename = basename($this->file_path);

        $path = $size === 'original'
            ? $this->file_path
            : "notices/images/{$size}/{$filename}";

        return Storage::disk('public')->exists($path)
            ? asset('storage/' . $path)
            : null;
    }
}
