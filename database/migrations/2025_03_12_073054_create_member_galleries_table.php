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
        Schema::create('member_galleries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id')->nullable();
            $table->bigInteger('club_id')->nullable();
            $table->string('gallery_name', 50)->nullable();
            $table->boolean('is_active')->nullable()->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_galleries');
    }
};
