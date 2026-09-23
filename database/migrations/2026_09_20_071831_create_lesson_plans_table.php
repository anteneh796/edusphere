<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('week_number')->default(1);
            $table->string('unit', 150)->nullable();
            $table->string('topic', 200);
            $table->text('objectives')->nullable();
            $table->text('materials')->nullable();
            $table->text('activities')->nullable();
            $table->text('assessment')->nullable();
            $table->text('homework')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | completed
            $table->date('scheduled_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
