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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->nullable();
            $table->string('featured_image', 250)->nullable();
            $table->string('name', 100)->nullable();
            $table->dateTime('event_date')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('event_type_id')->nullable();
            $table->smallInteger('event_kind_id')->nullable();
            $table->string('duration', 20)->nullable()->comment('2 hours, 10 hours, 10 days etc');
            $table->string('speaker', 25)->nullable();
            $table->string('speaker_club', 50)->nullable();
            $table->string('speaker_qualification', 50)->nullable();
            $table->enum('status', ['scheduled', 'postpond', 'tbc', 'cancelled', 'completed'])->nullable()->default('scheduled');
            $table->text('required_gear')->nullable();
            $table->text('tags_keywords')->nullable();
            $table->string('url', 250)->nullable();
            $table->text('rsvp_detail')->nullable();
            $table->boolean('enable_dropbox_upload')->nullable()->default(false);
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
        Schema::dropIfExists('events');
    }
};
