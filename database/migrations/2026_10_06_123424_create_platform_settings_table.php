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
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_recent_profiles')->default(true);
            $table->boolean('show_top_profiles')->default(true);
            $table->boolean('show_featured_profiles')->default(true);
            $table->boolean('show_recent_clubs')->default(true);
            $table->boolean('show_top_clubs')->default(true);
            $table->boolean('show_featured_clubs')->default(true);
            $table->boolean('show_contact_section')->default(false);
            $table->boolean('show_social_links')->default(true);
            $table->boolean('allow_club_signup')->default(true);
            $table->boolean('allow_photographer_signup')->default(true);
            $table->boolean('require_approval')->default(false);
            $table->boolean('custom_signup_enabled')->default(true);
            $table->text('custom_signup_message')->nullable();
            $table->boolean('cookie_consent_enabled')->default(true);
            $table->text('cookie_message')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
