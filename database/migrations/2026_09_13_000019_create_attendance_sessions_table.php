<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_room_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('taken_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('open');
            $table->string('note', 255)->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['class_room_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
