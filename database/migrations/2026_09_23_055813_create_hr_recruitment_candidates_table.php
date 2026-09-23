<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('applied_position', 120)->nullable();
            $table->string('cv_path', 255)->nullable();
            $table->text('top_skills')->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->date('interview_date')->nullable();
            $table->string('decision', 20)->nullable();
            $table->text('decision_notes')->nullable();
            $table->string('hiring_status', 20)->default('applied');
            $table->text('notes')->nullable();
            $table->foreignUuid('converted_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->index(['hiring_status', 'position_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};