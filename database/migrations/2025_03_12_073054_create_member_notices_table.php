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
        Schema::create('member_notices', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('member_id')->nullable();
            $table->smallInteger('notice_type_id')->nullable();
            $table->string('title', 250)->nullable();
            $table->text('description')->nullable();
            $table->string('tags', 250)->nullable();
            $table->string('link_page_url', 250)->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'draft', 'scheduled', 'deleted', 'pending'])->nullable()->default('scheduled');
            $table->enum('poll', ['none', 'anonymous', 'public'])->nullable()->default('none');
            $table->enum('urgency_importance', ['general', 'urgent', 'important'])->nullable()->default('general');
            $table->enum('comment_allowed', ['none', 'anonymous', 'public'])->nullable()->default('none');
            $table->string('notice_image', 250)->nullable();
            $table->string('notice_document', 250)->nullable();
            $table->boolean('is_active')->nullable()->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_notices');
    }
};
