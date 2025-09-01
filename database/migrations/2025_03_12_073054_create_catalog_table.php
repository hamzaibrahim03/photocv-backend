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
        Schema::create('catalog', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->string('name', 50)->nullable();
            $table->enum('catalog_type', ['competition_type', 'event_type', 'event_tag', 'judging_type', 'competition_theme', 'competition_catagories', 'competition_voting_method', 'competition_result_method', 'notice_type', 'news_type', 'page_type', 'max_image_per_gallery', 'max_image_file_size', 'max_image_width', 'max_image_height', 'booking_type', 'lead_source', 'service_type', 'deliverable_type', 'gear_category'])->nullable();
            $table->string('icon', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog');
    }
};
