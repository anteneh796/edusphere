<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_capacities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('grade_level_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('capacity')->default(0);
            $table->boolean('allow_override')->default(false);
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'grade_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_capacities');
    }
};
