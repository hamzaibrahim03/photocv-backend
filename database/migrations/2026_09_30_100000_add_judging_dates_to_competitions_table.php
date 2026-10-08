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
        // Judging window shown on the judge's competition detail screen
        Schema::table('competitions', function (Blueprint $table) {
            $table->dateTime('judging_start_date')->nullable()->after('submission_deadline');
            $table->dateTime('judging_end_date')->nullable()->after('judging_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['judging_start_date', 'judging_end_date']);
        });
    }
};
