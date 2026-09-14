<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_room_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('max_marks', 6, 2)->default(100);
            $table->decimal('pass_marks', 6, 2)->default(50);
            $table->decimal('weight', 6, 2)->nullable();
            $table->date('exam_date')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('instruction', 500)->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'class_room_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};
