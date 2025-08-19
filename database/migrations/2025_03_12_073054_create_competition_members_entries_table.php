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
        Schema::create('competition_members_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_comp_id');
            $table->enum('entry_type', ['print', 'digital'])->nullable();
            $table->string('entry_image_title', 250)->nullable();
            $table->string('entry_image', 250)->nullable();
            $table->integer('position')->nullable();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_members_entries');
    }
};
