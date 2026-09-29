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
        // Photos of the gear itself (uploaded files)
        Schema::create('gear_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gear_id')->constrained('gears')->cascadeOnDelete();
            $table->string('image_path', 250);
            $table->timestamps();
        });

        // Photos taken using the gear (picked from the member's gallery)
        Schema::create('gear_photo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gear_id')->constrained('gears')->cascadeOnDelete();
            $table->foreignId('photo_id')->constrained('photos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['gear_id', 'photo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gear_photo');
        Schema::dropIfExists('gear_images');
    }
};
