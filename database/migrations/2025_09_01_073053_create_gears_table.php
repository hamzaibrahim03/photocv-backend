<?php

// database/migrations/2025_09_01_073054_create_gears_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gears', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('category_id')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // file path or URL

            // Purchase & ownership
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('vendor')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('ownership_type')->nullable(); // Owned / Rented
            $table->date('warranty_expiry')->nullable();

            // Technical specs
            $table->string('sensor_type')->nullable();
            $table->string('focal_length')->nullable();
            $table->string('aperture')->nullable();
            $table->string('iso_range')->nullable();
            $table->string('weight')->nullable();
            $table->string('resolution')->nullable();

            // Photography niche suitability (0–100 scale sliders)
            $table->unsignedTinyInteger('suitability_wildlife')->default(0);
            $table->unsignedTinyInteger('suitability_sports')->default(0);
            $table->unsignedTinyInteger('suitability_portrait')->default(0);
            $table->unsignedTinyInteger('suitability_travel')->default(0);
            $table->unsignedTinyInteger('suitability_street')->default(0);
            $table->unsignedTinyInteger('suitability_wedding')->default(0);
            $table->unsignedTinyInteger('suitability_landscape')->default(0);
            $table->unsignedTinyInteger('suitability_event')->default(0);

            // Maintenance & status
            $table->date('last_serviced_at')->nullable();
            $table->string('condition')->nullable(); // New / Good / Needs Repair
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gears');
    }
};
