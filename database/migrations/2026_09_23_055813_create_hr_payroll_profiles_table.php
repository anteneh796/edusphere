<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('salary_grade', 30)->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->json('allowances')->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('account_number', 60)->nullable();
            $table->string('tax_id', 40)->nullable();
            $table->string('payment_method', 30)->default('bank_transfer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_profiles');
    }
};