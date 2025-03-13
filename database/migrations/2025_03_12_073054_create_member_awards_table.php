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
        Schema::create('member_awards', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('member_id')->nullable();
            $table->bigInteger('photo_id')->nullable();
            $table->string('award_standing', 250)->nullable()->comment('means, what type of award he achieved. Any medal , gold medal etc');
            $table->dateTime('award_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_awards');
    }
};
