<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('class_subject_id')->nullable()->constrained('class_subject')->nullOnDelete();
            $table->string('type', 20)->default('observation'); // observation|positive|concern
            $table->string('severity', 20)->default('info'); // info|warning|serious
            $table->date('recorded_on');
            $table->text('note');
            $table->text('action_taken')->nullable();
            $table->string('visibility', 20)->default('teacher'); // teacher|homeroom|admin
            $table->timestamps();

            $table->index(['student_id', 'recorded_on']);
            $table->index(['teacher_id', 'recorded_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_notes');
    }
};
