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
        Schema::table('photo_sales', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('photo_id');
            $table->enum('condition', ['new', 'used'])->nullable()->after('category');
            $table->string('image', 250)->nullable()->after('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photo_sales', function (Blueprint $table) {
            $table->dropColumn(['category', 'condition', 'image']);
        });
    }
};
