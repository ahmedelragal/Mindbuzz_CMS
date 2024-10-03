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
        Schema::create('teacher_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->nullable()->index('teacher_programs_teacher_id_foreign');
            $table->unsignedBigInteger('program_id')->nullable()->index('teacher_programs_program_id_foreign');
            $table->unsignedBigInteger('grade_id')->nullable()->index('teacher_programs_grade_id_foreign');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_programs');
    }
};
