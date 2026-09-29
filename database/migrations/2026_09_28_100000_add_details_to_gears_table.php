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
        Schema::table('gears', function (Blueprint $table) {
            // Basic gear information
            $table->string('system')->nullable()->after('category_id'); // e.g. Sony E, Canon RF
            $table->string('type')->nullable()->after('system');         // e.g. Prime Lens, Telephoto
            $table->string('model_name')->nullable()->after('type');
            $table->string('storage_name')->nullable()->after('model_name');

            // Purchase & ownership
            $table->boolean('insured')->nullable()->after('serial_number');
            $table->string('firmware_link', 500)->nullable()->after('insured');

            // Technical specs
            $table->string('mount')->nullable()->after('firmware_link');
            $table->decimal('dimensions', 8, 2)->nullable()->after('mount'); // diameter / length in mm
            $table->decimal('min_focal_length', 8, 2)->nullable()->after('dimensions');
            $table->decimal('max_focal_length', 8, 2)->nullable()->after('min_focal_length');
            $table->decimal('min_aperture', 5, 2)->nullable()->after('max_focal_length');
            $table->decimal('max_aperture', 5, 2)->nullable()->after('min_aperture');
            $table->decimal('min_focus_distance', 8, 2)->nullable()->after('max_aperture');
            $table->decimal('max_focus_distance', 8, 2)->nullable()->after('min_focus_distance');
            $table->decimal('max_magnification', 5, 2)->nullable()->after('max_focus_distance');

            // Additional photography niche suitability (0–100 scale sliders)
            $table->unsignedTinyInteger('suitability_walk_around')->default(0)->after('suitability_event');
            $table->unsignedTinyInteger('suitability_vlogging')->default(0)->after('suitability_walk_around');
            $table->unsignedTinyInteger('suitability_macro')->default(0)->after('suitability_vlogging');
            $table->unsignedTinyInteger('suitability_studio')->default(0)->after('suitability_macro');

            // Maintenance & status
            $table->unsignedInteger('shutter_count')->nullable()->after('suitability_studio');
            $table->string('priority_tag')->nullable()->after('shutter_count');
            $table->unsignedTinyInteger('rating')->nullable()->after('notes'); // 1–10

            // Sales
            $table->boolean('is_for_sale')->default(false)->after('rating');
            $table->decimal('sale_price', 10, 2)->nullable()->after('is_for_sale');
            $table->timestamp('sold_at')->nullable()->after('sale_price');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gears', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn([
                'system', 'type', 'model_name', 'storage_name',
                'insured', 'firmware_link',
                'mount', 'dimensions', 'min_focal_length', 'max_focal_length',
                'min_aperture', 'max_aperture', 'min_focus_distance', 'max_focus_distance',
                'max_magnification',
                'suitability_walk_around', 'suitability_vlogging', 'suitability_macro', 'suitability_studio',
                'shutter_count', 'priority_tag', 'rating',
                'is_for_sale', 'sale_price', 'sold_at',
            ]);
        });
    }
};
