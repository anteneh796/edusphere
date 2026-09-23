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
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_id', 30)->unique();
            $table->string('full_name', 150);
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id', 30)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('employment_type', 20)->default('full_time');
            $table->date('joining_date')->nullable();
            $table->foreignUuid('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employment_status', 20)->default('active');
            $table->string('university', 150)->nullable();
            $table->string('qualification', 150)->nullable();
            $table->string('degree', 150)->nullable();
            $table->string('specialization', 150)->nullable();
            $table->string('teaching_license', 100)->nullable();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->text('certifications')->nullable();
            $table->text('professional_skills')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employment_status', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};