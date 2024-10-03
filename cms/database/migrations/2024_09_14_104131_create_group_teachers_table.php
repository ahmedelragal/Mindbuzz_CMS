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
        Schema::create('group_teachers', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('group_id')->nullable()->unsigned();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            
            $table->bigInteger('teacher_id')->nullable()->unsigned();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            
            $table->bigInteger('co_teacher_id')->nullable()->unsigned();
            $table->foreign('co_teacher_id')->references('id')->on('users')->nullOnDelete();
            
            $table->bigInteger('program_id')->nullable()->unsigned();
            $table->foreign('program_id')->references('id')->on('programs')->nullOnDelete();
            
            $table->bigInteger('stage_id')->nullable()->unsigned();
            $table->foreign('stage_id')->references('id')->on('stages')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_teachers');
    }
};
