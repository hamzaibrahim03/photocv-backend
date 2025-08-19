<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionGlobalSetting extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'club_id'];

    protected $fillable = [
        'club_id',
        'competition_type_id',
        'judging_type_id',
        'status',
        'max_entries_print',
        'max_entries_digital',
        'allowed_image_formats',
        'max_file_size',
        'category_id',
        'theme_id',
        'print_vs_digital',
        'color_vs_mono',
        'voting_method_id',
        'top_places',
        'high_commendation_number',
        'commendation_number',
        'points_first_place',
        'points_second_place',
        'points_third_place',
        'points_high_commendation',
        'points_commendation',
        'comments_and_critique',
        'auto_generate_certificates',
        'visible_judges_feedback'
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
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

}
