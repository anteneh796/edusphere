<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 30)->default('general')->index();
            $table->string('full_name', 120);
            $table->string('email', 120);
            $table->string('phone', 30)->nullable();
            $table->string('student_name', 120)->nullable();
            $table->string('grade_level', 60)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->timestamp('handled_at')->nullable();
            $table->uuid('handled_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
