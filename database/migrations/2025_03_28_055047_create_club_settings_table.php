<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('club_settings', function (Blueprint $table) {
            $table->id();
            $table->string('club_name');
            $table->string('club_tagline')->nullable();
            $table->text('about')->nullable();
            $table->text('contact_details')->nullable();
            $table->enum('domain_type', ['Custom', 'Subdomain']);
            $table->string('domain_name')->nullable();
            $table->string('timezone');
            $table->date('date');
            $table->enum('club_privacy', ['Public', 'Members Only', 'Private']);
            $table->string('typography_fonts')->nullable();
            $table->string('specific_colors')->nullable();
            $table->string('header_customization')->nullable();
            $table->string('footer_customization')->nullable();
            $table->string('logo')->nullable();
            $table->string('club_banner')->nullable();
            $table->enum('registration_access_control', ['Open', 'Invite Only', 'Manual Approval']);
            $table->enum('members_directory_visibility', ['Visible', 'Club Only']);
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('likes_enabled')->default(true);
            $table->enum('website_sections', ['Enable/Disable News', 'Events', 'Galleries', 'Competitions']);
            $table->enum('homepage_content_blocks', ['All', 'Fewer', 'Fewest']);
            $table->enum('reminders', ['All', 'Some', 'None']);
            $table->string('facebook_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('flickr_link')->nullable();
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
