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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('club_name', 250);
            $table->string('tag_line', 250)->nullable();
            $table->string('typography', 250)->nullable();
            $table->string('contact_details', 250)->nullable();
            $table->enum('domain_type', ['custom', 'subdomain'])->nullable();
            $table->string('domain_name', 250)->nullable();
            $table->string('time_zone', 250)->nullable();
            $table->string('font', 100)->nullable();
            $table->string('background_color', 10)->nullable();
            $table->text('header_text')->nullable();
            $table->text('about_title')->nullable();
            $table->text('about_description')->nullable();
            $table->string('about_img')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('footer_img')->nullable();
            $table->string('logo', 250)->nullable();
            $table->string('cover_image', 250)->nullable();
            $table->enum('registration', ['open', 'invite', 'manual_approval'])->nullable()->default('open');
            $table->enum('directory_visibility', ['visible', 'club_only'])->nullable()->comment('Member directory public visibility');
            $table->enum('comments', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('likes', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('news', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('events', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('galleries', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('competitions', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('home_page_blocks', ['all', 'fewer', 'fewest'])->nullable()->default('all');
            $table->enum('reminders', ['all', 'some', 'none'])->nullable()->default('all');
            $table->string('fb_link', 250)->nullable();
            $table->string('insta_link', 250)->nullable();
            $table->string('flickr_link', 250)->nullable();
            $table->text('privacy_policy')->nullable()->comment('GDPR and privacy management');
            $table->text('user_consent_cookie')->nullable();
            $table->text('data_collection_preference')->nullable();
            $table->enum('content_moderation_reporting', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
