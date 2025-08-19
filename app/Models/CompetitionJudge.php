<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompetitionJudge extends Pivot
{
    use HasFactory;

    protected $table = 'competition_judges';

    protected $fillable = [
        'competition_id',
        'user_id',
        'role',
        'assigned_at',
    ];
}
