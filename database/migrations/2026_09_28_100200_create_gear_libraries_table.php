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
        // A named kit of gear, e.g. "Night Wedding Kit"
        Schema::create('gear_libraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Camera, lenses and accessories that make up a kit
        Schema::create('gear_library_gear', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gear_library_id')->constrained('gear_libraries')->cascadeOnDelete();
            $table->foreignId('gear_id')->constrained('gears')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['gear_library_id', 'gear_id']);
        });

        // Kits picked for a booking ("Pick from Library")
        Schema::create('booking_gear_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('gear_library_id')->constrained('gear_libraries')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['booking_id', 'gear_library_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_gear_library');
        Schema::dropIfExists('gear_library_gear');
        Schema::dropIfExists('gear_libraries');
    }
};
