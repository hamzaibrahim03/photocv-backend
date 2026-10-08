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
        Schema::create('club_config_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->string('group', 50);
            $table->string('name');
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->cascadeOnDelete();
            $table->index(['club_id', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_config_items');
    }
};
