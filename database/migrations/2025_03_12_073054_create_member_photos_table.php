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
        Schema::create('member_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gallery_id');
            $table->string('title', 250)->nullable();
            $table->text('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->text('club_admin_notes')->nullable();
            $table->boolean('allow_cc')->nullable()->default(false);
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('gallery_id')->references('id')->on('member_galleries')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_photos');
    }
};
