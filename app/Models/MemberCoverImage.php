<?php

// app/Models/MemberCoverImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCoverImage extends Model
{
    protected $fillable = ['member_id', 'image_path', 'position'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
