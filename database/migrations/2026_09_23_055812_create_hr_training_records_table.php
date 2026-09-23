<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->string('course_name', 150);
            $table->string('provider', 150)->nullable();
            $table->date('trained_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->string('certificate_path')->nullable();
            $table->unsignedSmallInteger('hours')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_records');
    }
};