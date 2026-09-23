<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('application_number', 30)->unique();
            $table->string('status', 30)->default('inquiry')->index();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('previous_school', 150)->nullable();
            $table->foreignUuid('intake_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignUuid('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('source_inquiry_id')->nullable()->constrained('inquiries')->nullOnDelete();
            $table->foreignUuid('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('parent_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision')->nullable();
            $table->text('decision_comment')->nullable();
            $table->foreignUuid('decision_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waitlisted_at')->nullable();
            $table->unsignedInteger('waitlist_position')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'grade_level_id']);
            $table->index(['status', 'intake_academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
