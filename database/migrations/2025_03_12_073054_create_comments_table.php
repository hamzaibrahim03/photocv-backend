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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('record_id')->nullable();
            $table->enum('record_type', ['page', 'news', 'notice', 'event', 'photo', 'competition_entry']);
            $table->enum('comment_type', ['comment', 'liking'])->nullable()->default('liking');
            $table->text('comment')->nullable();
            $table->bigInteger('interacted_by')->nullable()->comment('the user who commented');
            $table->boolean('is_published')->nullable()->default(true);
            $table->string('admin_notes', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
