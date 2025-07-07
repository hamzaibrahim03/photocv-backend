<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberNote extends Model
{
    protected $fillable = [
        'title', 'description', 'type',
    ];
}
