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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 20)->unique();
            $table->string('first_name', 20)->nullable();
            $table->string('last_name', 20)->nullable();
            $table->string('domain_name', 250)->nullable();
            $table->string('tag_line', 250)->nullable();
            $table->text('about')->nullable();
            $table->string('email', 50)->unique()->nullable();
            $table->string('password', 250)->nullable();
            $table->string('profile_image', 250)->nullable();
            $table->string('address', 250)->nullable();
            $table->string('ciy', 50)->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('country', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('bio')->nullable();
            $table->string('status', 15)->nullable()->default('pending')->comment('approved,pending,suspend');
            $table->string('account_status', 25)->nullable()->comment('active,inactive');
            $table->dateTime('approval_date')->nullable();
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
        Schema::dropIfExists('users');
    }
};
