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
        // Gallery upload "Image Settings" and "EXIF Details"
        Schema::table('photos', function (Blueprint $table) {
            $table->boolean('allow_comments')->default(true)->after('allow_cc');
            $table->boolean('allow_likes')->default(true)->after('allow_comments');
            $table->enum('visibility', ['public', 'private'])->default('public')->after('allow_likes');
            $table->boolean('show_in_portfolio')->default(true)->after('visibility');
            $table->boolean('show_exif')->default(true)->after('show_in_portfolio');
        });

        // Booking reminders: up to 5 "days before event" values + pre-event reminder
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('event_reminders')->nullable()->after('set_reminder');
            $table->boolean('pre_event_reminder')->default(false)->after('event_reminders');
        });

        // Planned day out cards show a status and the gear to take
        Schema::table('planned_day_outs', function (Blueprint $table) {
            $table->enum('status', ['upcoming', 'pending', 'visited'])->default('upcoming')->after('location');
            $table->string('gear')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_day_outs', function (Blueprint $table) {
            $table->dropColumn(['status', 'gear']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['event_reminders', 'pre_event_reminder']);
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['allow_comments', 'allow_likes', 'visibility', 'show_in_portfolio', 'show_exif']);
        });
    }
};
