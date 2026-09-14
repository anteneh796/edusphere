<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->date('enrolled_at');
            $table->date('left_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
            $table->index(['academic_year_id', 'grade_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
