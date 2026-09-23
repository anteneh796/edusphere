<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_room_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->nullable()->constrained('class_subject')->nullOnDelete();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon .. 7=Sun
            $table->unsignedSmallInteger('period_number');
            $table->string('room', 50)->nullable();
            $table->timestamps();

            $table->unique(['class_room_id', 'day_of_week', 'period_number'], 'uc_timetable_class_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};
