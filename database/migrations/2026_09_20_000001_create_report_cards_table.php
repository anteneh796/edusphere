<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUuid('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignUuid('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();

            $table->string('term_name', 100)->nullable();
            $table->tinyInteger('term_sequence')->nullable();

            /* Workflow status (ReportCardStatus enum) */
            $table->string('status', 20)->default('draft');

            /* Natural identifier (ReportCardNumber badge) */
            $table->string('report_card_number', 40)->nullable();

            /* Academic summary snapshot (decimals per sibling conventions) */
            $table->decimal('total_max_marks', 8, 2)->default(0);
            $table->decimal('total_obtained_marks', 8, 2)->default(0);
            $table->decimal('average_percent', 6, 2)->nullable();
            $table->string('overall_grade', 4)->nullable();
            $table->json('grade_summary')->nullable();

            /* Attendance snapshot (per-subject attendance captured in items) */
            $table->unsignedSmallInteger('attendance_total')->default(0);
            $table->unsignedSmallInteger('attendance_present')->default(0);
            $table->unsignedSmallInteger('attendance_absent')->default(0);

            $table->string('teacher_comment', 1000)->nullable();
            $table->string('principal_remarks', 1000)->nullable();

            $table->string('promotion_status', 20)->nullable();

            /* Durable audit trail */
            $table->foreignUuid('generated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('published_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'academic_term_id', 'student_id', 'exam_id'],
                'report_cards_year_term_student_exam_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
