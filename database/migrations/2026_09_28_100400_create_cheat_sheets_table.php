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
        Schema::create('cheat_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable(); // catalog: cheat_sheet_category
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('reference_image', 250)->nullable();

            // Manual settings (ranges)
            $table->string('shutter_min', 20)->nullable(); // e.g. 1/160
            $table->string('shutter_max', 20)->nullable();
            $table->unsignedInteger('iso_min')->nullable();
            $table->unsignedInteger('iso_max')->nullable();
            $table->decimal('aperture_min', 5, 2)->nullable(); // f-number
            $table->decimal('aperture_max', 5, 2)->nullable();

            // Essential settings
            $table->text('lens')->nullable();
            $table->text('camera')->nullable();
            $table->text('white_balance')->nullable();
            $table->text('noise_reduction')->nullable();

            $table->longText('notes')->nullable(); // rich text
            $table->json('accessories')->nullable();
            $table->json('tags')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->timestamps();

            $table->index(['user_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheat_sheets');
    }
};
