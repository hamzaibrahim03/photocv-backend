<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberNoticeFile extends Model
{
    use HasFactory;

    protected $table = 'member_notice_files';

    protected $fillable = [
        'member_notice_id',
        'file_name',
        'file_type',
        'file_path',
    ];

    // Relationship with MemberNotice
    public function notice()
    {
        return $this->belongsTo(MemberNotice::class, 'member_notice_id');
    }
}
