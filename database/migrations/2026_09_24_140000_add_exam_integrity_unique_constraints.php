<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table): void {
            $table->unique(
                ['exam_id', 'class_room_id', 'subject_id'],
                'exam_subjects_exam_class_subject_unique'
            );
        });

        Schema::table('exam_results', function (Blueprint $table): void {
            $table->unique(
                ['exam_subject_id', 'student_id'],
                'exam_results_paper_student_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table): void {
            $table->dropUnique('exam_results_paper_student_unique');
        });

        Schema::table('exam_subjects', function (Blueprint $table): void {
            $table->dropUnique('exam_subjects_exam_class_subject_unique');
        });
    }
};
