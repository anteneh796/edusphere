<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('internal');
            $table->foreignUuid('from_class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();
            $table->foreignUuid('to_class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();
            $table->date('transfer_date');
            $table->string('destination_school', 150)->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('certificate_number', 50)->nullable();
            $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
