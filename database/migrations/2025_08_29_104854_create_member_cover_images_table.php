<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_cover_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('image_path', 250);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['member_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_cover_images');
    }
};
