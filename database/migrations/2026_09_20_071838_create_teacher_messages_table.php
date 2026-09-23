<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_type', 30)->default('guardian'); // guardian|student|teacher|principal|admin
            $table->foreignUuid('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('class_room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject', 160);
            $table->text('body');
            $table->string('message_type', 20)->default('individual'); // individual|announcement|academic|emergency
            $table->string('status', 20)->default('sent'); // draft|sent
            $table->timestamps();

            $table->index(['sender_id', 'created_at']);
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_messages');
    }
};
