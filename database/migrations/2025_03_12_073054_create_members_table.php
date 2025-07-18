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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('header_image', 250)->nullable();
            $table->enum('club_privacy', ['public', 'private', 'member_only'])->nullable()->default('public');
            $table->enum('profile_privacy', ['public', 'private'])->nullable()->default('public');
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('background_color', 10)->nullable();
            $table->string('font', 100)->nullable();
            $table->string('logo', 250)->nullable();
            $table->string('cover_image', 250)->nullable();
            $table->string('color_theme', 250)->nullable();
            $table->json('social_links_visibility')->nullable();
            $table->text('user_consent_cookie')->nullable();
            $table->text('data_collection_preference')->nullable();
            $table->enum('content_moderation_reporting', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
