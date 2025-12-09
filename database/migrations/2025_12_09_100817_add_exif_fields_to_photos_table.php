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
        Schema::table('photos', function (Blueprint $table) {

            $table->string('camera_model')->nullable();
            $table->string('lens')->nullable();
            $table->string('focal_length')->nullable();
            $table->string('aperture')->nullable();
            $table->string('shutter_speed')->nullable();
            $table->string('iso')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('color_type')->nullable();
            $table->integer('bit_depth')->nullable();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            //
        });
    }
};
