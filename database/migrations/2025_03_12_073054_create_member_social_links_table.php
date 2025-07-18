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
        Schema::create('member_social_links', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id')->nullable();
            $table->string('social_media_name', 20)->nullable();
            $table->string('social_link', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_social_links');
    }
};
