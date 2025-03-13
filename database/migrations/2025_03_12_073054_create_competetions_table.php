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
        Schema::create('competetions', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('name', 100)->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('competetion_type_id')->nullable();
            $table->smallInteger('judging_type_id')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('submission_deadline')->nullable()->comment('2 hours, 10 hours, 10 days etc');
            $table->smallInteger('max_entries_print')->nullable();
            $table->smallInteger('max_entries_digital')->nullable();
            $table->string('allowed_image_formats', 250)->nullable();
            $table->tinyInteger('max_file_size')->nullable()->comment('size in MBs');
            $table->enum('status', ['scheduled', 'postpond', 'draft', 'cancelled', 'completed'])->nullable()->default('scheduled');
            $table->smallInteger('category_id')->nullable();
            $table->smallInteger('theme_id')->nullable();
            $table->enum('print_vs_digital', ['print', 'digital'])->nullable();
            $table->enum('color_vs_mono', ['color', 'monochrome'])->nullable();
            $table->text('judging_panel')->nullable();
            $table->smallInteger('voting_method_id')->nullable();
            $table->smallInteger('result_method_id')->nullable();
            $table->dateTime('result_announcement_date')->nullable();
            $table->string('top_places', 20)->nullable();
            $table->tinyInteger('high_commendation_number')->nullable();
            $table->tinyInteger('commendation_number')->nullable();
            $table->text('prizes')->nullable();
            $table->boolean('cc_allowed')->nullable()->default(false);
            $table->boolean('auto_certificate')->nullable()->default(false);
            $table->boolean('alllow_judges_feedback')->nullable()->default(false);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competetions');
    }
};
