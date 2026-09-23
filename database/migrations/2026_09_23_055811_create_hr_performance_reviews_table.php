<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->string('period', 50);
            $table->foreignUuid('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('teaching_quality')->nullable();
            $table->unsignedTinyInteger('classroom_management')->nullable();
            $table->unsignedTinyInteger('professional_conduct')->nullable();
            $table->unsignedTinyInteger('attendance_score')->nullable();
            $table->unsignedTinyInteger('student_engagement')->nullable();
            $table->unsignedTinyInteger('admin_responsibility')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};