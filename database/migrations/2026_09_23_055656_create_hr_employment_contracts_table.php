<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number', 40)->unique();
            $table->string('employment_type', 20)->default('full_time');
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('salary_grade', 30)->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->unsignedSmallInteger('working_hours_per_week')->nullable();
            $table->string('renewal_status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};