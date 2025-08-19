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
        Schema::create('competition_global_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->nullable();
            $table->unsignedBigInteger('competition_type_id')->nullable();
            $table->unsignedBigInteger('judging_type_id')->nullable();
            $table->enum('status', ['scheduled', 'postpond', 'draft', 'cancelled', 'completed'])->default('scheduled');
            $table->smallInteger('max_entries_print')->nullable();
            $table->smallInteger('max_entries_digital')->nullable();
            $table->string('allowed_image_formats', 250)->nullable();
            $table->tinyInteger('max_file_size')->nullable()->comment('size in MBs');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('theme_id')->nullable();
            $table->enum('print_vs_digital', ['print', 'digital'])->nullable();
            $table->enum('color_vs_mono', ['color', 'monochrome'])->nullable();
            $table->unsignedBigInteger('voting_method_id')->nullable();
            $table->string('top_places', 20)->nullable();
            $table->tinyInteger('high_commendation_number')->nullable();
            $table->tinyInteger('commendation_number')->nullable();
            $table->tinyInteger('points_first_place')->nullable();
            $table->tinyInteger('points_second_place')->nullable();
            $table->tinyInteger('points_third_place')->nullable();
            $table->tinyInteger('points_high_commendation')->nullable();
            $table->tinyInteger('points_commendation')->nullable();
            $table->boolean('comments_and_critique')->default(false);
            $table->boolean('auto_generate_certificates')->default(false);
            $table->boolean('visible_judges_feedback')->default(false);
            $table->timestamps();

            if (Schema::hasTable('clubs')) {
                $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_global_settings');
    }
};
