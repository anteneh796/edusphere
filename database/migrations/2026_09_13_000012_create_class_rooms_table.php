<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 30);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->timestamps();

            $table->unique(['grade_level_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};
