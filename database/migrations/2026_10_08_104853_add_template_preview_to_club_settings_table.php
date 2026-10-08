<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->string('template_preview')->nullable();
            $table->timestamp('template_preview_generated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->dropColumn(['template_preview', 'template_preview_generated_at']);
        });
    }
};
