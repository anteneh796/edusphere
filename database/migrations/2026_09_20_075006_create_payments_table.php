<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('payment_number', 30)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('method', 20)->default('other');
            $table->string('status', 20)->default('pending');
            $table->string('reference', 80)->nullable();
            $table->string('provider', 40)->nullable();
            $table->string('provider_reference', 80)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignUuid('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status']);
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
