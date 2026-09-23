<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('homework_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('pending'); // pending | submitted | late | graded
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignUuid('graded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['homework_assignment_id', 'student_id'], 'uc_homework_submission_pair');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};
