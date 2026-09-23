<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            /* Promotion flow: proposed -> pending -> (promoted | retained | completed) */
            $table->string('status', 20)->default('pending');

            $table->foreignUuid('from_class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();
            $table->foreignUuid('to_class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();
            $table->unsignedTinyInteger('from_grade')->nullable();
            $table->unsignedTinyInteger('to_grade')->nullable();

            $table->string('remarks', 1000)->nullable();

            $table->foreignUuid('decided_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id'], 'promotions_student_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
