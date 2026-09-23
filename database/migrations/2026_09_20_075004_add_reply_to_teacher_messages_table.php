<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_messages', function (Blueprint $table) {
            $table->foreignUuid('reply_to_id')->nullable()->after('status')->constrained('teacher_messages')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable()->after('reply_to_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_id');
            $table->dropColumn('read_at');
        });
    }
};
