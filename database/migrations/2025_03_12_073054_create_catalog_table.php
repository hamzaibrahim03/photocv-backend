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
        Schema::create('catalog', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->string('name', 50)->nullable();
            $table->enum('catalog_type', ['competition_type', 'event_type', 'event_kind', 'judging_type', 'competition_theme', 'competition_catagories', 'competition_voting_method', 'competition_result_method', 'notice_type', 'news_type'])->nullable();
            $table->string('icon', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog');
    }
};
