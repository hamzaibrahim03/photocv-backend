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
        Schema::create('member_notice_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_notice_id');
            $table->string('file_name', 250);
            $table->string('file_type', 50);
            $table->string('file_path', 500);
            $table->timestamps();

            $table->foreign('member_notice_id')->references('id')->on('member_notices')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_notice_files');
    }
};
