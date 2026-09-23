<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('meeting_type', 20)->default('in_person');
            $table->string('reason')->nullable();
            $table->date('preferred_date');
            $table->string('preferred_time', 10)->nullable();
            $table->string('status', 20)->default('requested');
            $table->string('staff_note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['teacher_id', 'status']);
            $table->index(['guardian_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_requests');
    }
};
