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
        // "My Wish list" and "Gift List" items
        Schema::create('gear_wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('list_type', ['wish', 'gift'])->default('wish');
            $table->string('title');
            $table->unsignedBigInteger('category_id')->nullable(); // catalog: gear_category
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image', 250)->nullable();
            $table->string('link', 500)->nullable();
            $table->boolean('is_purchased')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'list_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gear_wishlists');
    }
};
