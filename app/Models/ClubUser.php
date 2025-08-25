<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubUser extends Model
{
    protected $table = 'club_user';
    protected $fillable = ['user_id', 'club_id', 'status', 'joined_at', 'rejected_at'];
}
