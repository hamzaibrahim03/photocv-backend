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
        Schema::create('club_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('news_type_id');
            $table->text('short_description')->nullable();
            $table->longText('description');
            $table->string('thumb_image')->nullable();
            $table->date('publish_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_news');
    }
};
