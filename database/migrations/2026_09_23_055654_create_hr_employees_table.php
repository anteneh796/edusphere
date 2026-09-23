<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_id', 40)->unique();
            $table->string('full_name', 150);
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id', 80)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('employment_type', 30)->default('full_time');
            $table->date('joining_date')->nullable();
            $table->foreignUuid('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employment_status', 30)->default('active');
            $table->string('university', 150)->nullable();
            $table->string('qualification', 150)->nullable();
            $table->string('degree', 100)->nullable();
            $table->string('specialization', 150)->nullable();
            $table->string('teaching_license', 100)->nullable();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->text('certifications')->nullable();
            $table->text('professional_skills')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'employment_status']);
            $table->index(['position_id', 'employment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
