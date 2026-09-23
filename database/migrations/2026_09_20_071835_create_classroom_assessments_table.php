<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('class_room_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->string('title', 200);
            $table->string('type', 30)->default('quiz'); // quiz|classwork|homework|practical|oral|project
            $table->string('status', 20)->default('draft'); // draft|published|closed|graded
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->date('assessment_date')->nullable();
            $table->string('room', 50)->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['class_subject_id', 'status']);
            $table->index(['teacher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_assessments');
    }
};
