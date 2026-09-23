<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->text('instructions')->nullable();
            $table->date('assigned_on');
            $table->date('due_on');
            $table->unsignedSmallInteger('max_marks')->nullable();
            $table->string('visibility', 20)->default('class'); // class | grade | school
            $table->string('status', 20)->default('draft'); // draft | published | closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_assignments');
    }
};
