<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->foreignUuid('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('reference_number', 30)->unique();
            $table->string('type', 40);
            $table->string('subject', 190);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->foreignUuid('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution')->nullable();
            $table->string('staff_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['guardian_id', 'status']);
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_requests');
    }
};
