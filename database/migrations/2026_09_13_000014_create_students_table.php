<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('student_number', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->string('gender', 10);
            $table->date('date_of_birth');
            $table->string('photo_path')->nullable();
            $table->string('national_id', 30)->nullable();
            $table->string('status', 20)->default('new');
            $table->date('enrollment_date');
            $table->string('previous_school', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('health_notes')->nullable();
            $table->foreignUuid('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('class_room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('guardian_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'grade_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
