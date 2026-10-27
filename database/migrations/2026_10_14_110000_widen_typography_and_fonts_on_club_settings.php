<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * typography/fonts were VARCHAR(10), enough for neither a real font
     * family name (e.g. "Racing Sans One") nor the JSON font-combo blob the
     * Appearance & Branding step now saves.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE club_settings MODIFY typography VARCHAR(100) NULL");
        DB::statement("ALTER TABLE club_settings MODIFY fonts TEXT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE club_settings MODIFY typography VARCHAR(10) NULL");
        DB::statement("ALTER TABLE club_settings MODIFY fonts VARCHAR(10) NULL");
    }
};
