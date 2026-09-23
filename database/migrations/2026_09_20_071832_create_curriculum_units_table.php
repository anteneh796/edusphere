<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('total_lessons')->default(1);
            $table->unsignedTinyInteger('covered_lessons')->default(0);
            $table->string('status', 20)->default('pending'); // pending | in_progress | completed
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['class_subject_id', 'position'], 'uc_curriculum_class_subject_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_units');
    }
};
