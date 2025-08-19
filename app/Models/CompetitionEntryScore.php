<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionEntryScore extends Model
{
    protected $table = 'competition_entry_scores';
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'entry_id',
        'judge_id',
        'score',
        'comment'
    ];

    public function entry()
    {
        return $this->belongsTo(CompetitionMembersEntry::class, 'entry_id');
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }
}
