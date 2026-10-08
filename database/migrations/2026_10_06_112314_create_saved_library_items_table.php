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
        Schema::create('saved_library_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->enum('category', ['Events', 'Competitions', 'Galleries', 'Notices', 'News']);
            $table->bigInteger('item_id');
            $table->string('title', 250)->nullable();
            $table->text('description')->nullable();
            $table->string('date', 50)->nullable();
            $table->string('image', 250)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'category', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_library_items');
    }
};
