<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('classroom_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('marks_obtained')->default(0);
            $table->string('grade', 5)->nullable();
            $table->string('status', 20)->default('entered'); // entered|published
            $table->string('remarks', 255)->nullable();
            $table->foreignUuid('entered_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['classroom_assessment_id', 'student_id'], 'uc_assessment_result_pair');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
    }
};
