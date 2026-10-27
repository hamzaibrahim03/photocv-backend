<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('photo_id')->nullable();
            $table->string('title', 250);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 8)->default('GBP');
            $table->string('status', 20)->default('active'); // active | inactive
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('photo_id')->references('id')->on('photos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_sales');
    }
};
