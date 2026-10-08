<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * website_sections was a single-value enum, but Step4Content.jsx lets a
     * club admin enable multiple sections via checkboxes (stored comma-
     * joined), so it needs to hold more than one enum option at once.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE club_settings MODIFY website_sections VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE club_settings MODIFY website_sections ENUM('News','Events','Galleries','Competitions') NOT NULL DEFAULT 'News'");
    }
};
