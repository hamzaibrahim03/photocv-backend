<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('club_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            
            $table->string('timezone')->nullable();
            $table->date('date')->nullable();
            $table->enum('club_privacy', ['Public', 'Members Only', 'Private']);

            $table->string('theme_colors')->nullable();
            $table->string('text_color', 10)->nullable();
            $table->string('primary_color', 10)->nullable();
            $table->string('background_color', 10)->nullable();
            $table->string('secondary_color', 10)->nullable();
            $table->string('accent_color', 10)->nullable();
            $table->string('typography', 10)->nullable();
            $table->string('fonts', 10)->nullable();

            $table->text('header_title')->nullable();
            $table->text('header_description')->nullable();
            $table->string('header_img')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('footer_img')->nullable();
            $table->text('footer_description')->nullable();
            $table->string('logo', 250)->nullable();
            // $table->string('cover_image', 250)->nullable();

            
            $table->enum('registration', ['open', 'invite', 'manual_approval'])->nullable()->default('open');
            $table->enum('directory_visibility', ['visible', 'club_only'])->nullable()->comment('Member directory public visibility');
            $table->enum('comments', ['enabled', 'disabled'])->nullable()->default('disabled');
            $table->enum('likes', ['enabled', 'disabled'])->nullable()->default('disabled');

            $table->enum('website_sections', ['News', 'Events', 'Galleries', 'Competitions']);
            $table->enum('comment_preference', ['All', 'Fewer', 'Fewest']);
            $table->enum('reminders', ['all', 'some', 'none'])->nullable()->default('all');
            $table->boolean('fb_link_option')->default(false);
            $table->string('fb_link', 250)->nullable();
            $table->boolean('insta_link_option')->default(false);
            $table->string('insta_link', 250)->nullable();
            $table->boolean('flickr_link_option')->default(false);
            $table->string('flickr_link', 250)->nullable();

            $table->string('gdpr_privacy_policy_management')->nullable();
            $table->boolean('cookies')->default(false);
            $table->text('cookies_description')->nullable();
            $table->boolean('data_collection_preferences')->default(false);
            $table->text('data_collection_preferences_description')->nullable();
            $table->boolean('allow_reporting')->default(false);
            $table->text('allow_reporting_description')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('club_settings');
    }
};
