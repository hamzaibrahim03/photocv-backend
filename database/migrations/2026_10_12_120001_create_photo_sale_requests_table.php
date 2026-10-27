<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_sale_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('photo_sale_id');
            $table->unsignedBigInteger('requester_id');
            $table->string('status', 20)->default('pending'); // pending | accepted | rejected
            $table->timestamps();

            $table->foreign('photo_sale_id')->references('id')->on('photo_sales')->onDelete('cascade');
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_sale_requests');
    }
};
