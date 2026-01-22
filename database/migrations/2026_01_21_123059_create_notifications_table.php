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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // receiver
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); // who did it

            $table->string('category'); // social, profile, club, system
            $table->string('type');     // notice_liked, event_commented, entry_commented, etc.

            $table->nullableMorphs('notifiable'); // notifiable_type + notifiable_id

            $table->json('data');
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['category', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
