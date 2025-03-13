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
        Schema::create('member_clubs', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('member_id')->nullable();
            $table->bigInteger('club_id')->nullable();
            $table->dateTime('joining_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_clubs');
    }
};
