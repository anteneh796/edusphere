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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            /* TranscriptStatus enum: draft / active / completed */
            $table->string('status', 20)->default('draft');

            $table->string('year_level_label', 100)->nullable();
            $table->unsignedTinyInteger('term_count')->default(0);

            /* Academic snapshot (decimals per sibling conventions) */
            $table->decimal('total_max_marks', 8, 2)->default(0);
            $table->decimal('total_obtained_marks', 8, 2)->default(0);
            $table->decimal('average_percent', 6, 2)->nullable();
            $table->string('overall_grade', 4)->nullable();

            $table->foreignUuid('generated_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id'], 'transcripts_student_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
