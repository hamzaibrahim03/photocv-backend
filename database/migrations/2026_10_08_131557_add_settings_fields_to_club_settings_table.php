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
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('homepage_content_blocks')->nullable();
            $table->unsignedInteger('max_images_per_gallery')->nullable();
            $table->decimal('max_image_file_size', 8, 2)->nullable();
            $table->unsignedInteger('max_image_width')->nullable();
            $table->unsignedInteger('max_image_height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_email', 'contact_phone', 'contact_address', 'homepage_content_blocks',
                'max_images_per_gallery', 'max_image_file_size', 'max_image_width', 'max_image_height',
            ]);
        });
    }
};
