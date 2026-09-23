<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_number', 40)->unique();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->string('letter_type', 30);
            $table->string('title', 150);
            $table->text('content');
            $table->foreignUuid('issued_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issued_on');
            $table->timestamps();

            $table->index(['employee_id', 'letter_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_letters');
    }
};