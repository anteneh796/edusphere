<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_communications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('direction', 10)->default('outbound');
            $table->string('contact', 150)->nullable();
            $table->string('subject', 200)->nullable();
            $table->text('message')->nullable();
            $table->timestamp('occurred_at');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['application_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_communications');
    }
};
