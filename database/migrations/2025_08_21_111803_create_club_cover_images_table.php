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
        Schema::create('club_cover_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_setting_id')->constrained('club_settings')->onDelete('cascade');
            $table->string('image_path', 250);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_cover_images');
    }
};
