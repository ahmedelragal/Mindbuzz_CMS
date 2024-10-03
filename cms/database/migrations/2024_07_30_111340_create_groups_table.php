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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('sec_name')->nullable();
            $table->bigInteger('school_id')->nullable()->unsigned();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('stage_id')->references('id')->on('stages');
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->foreign('program_id')->references('id')->on('programs');
            $table->unsignedBigInteger('program_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->text('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
