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
        Schema::create('competition_entry_scores', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('entry_id');
            $table->bigInteger('judge_id');
            $table->integer('score')->nullable();
            $table->text('comment')->nullable();
            $table->enum('position', ['1', '2', '3'])->nullable();
            $table->boolean('is_bookmarked')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entry_id', 'judge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_entry_scores');
    }
};
