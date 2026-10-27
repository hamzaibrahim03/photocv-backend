<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * position was a 3-value enum ('1','2','3'), but judges can award
     * positions 1-9 plus Commended/Highly Commended/Intermediate (C/H/I)
     * per JudgeImageScoring.jsx, so it needs to hold any of those codes.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE competition_entry_scores MODIFY position VARCHAR(2) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE competition_entry_scores MODIFY position ENUM('1','2','3') NULL");
    }
};
