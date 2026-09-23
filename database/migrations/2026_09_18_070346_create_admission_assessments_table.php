<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->string('type', 30);
            $table->timestamp('scheduled_at');
            $table->string('location', 150)->nullable();
            $table->string('status', 30)->default('scheduled')->index();
            $table->timestamp('conducted_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_assessments');
    }
};
