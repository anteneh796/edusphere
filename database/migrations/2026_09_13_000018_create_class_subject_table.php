<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_subject', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_room_id')->constrained('class_rooms')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('periods_per_week')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['class_room_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subject');
    }
};
