<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('report_card_items', 'teacher_comment')) {
            Schema::table('report_card_items', function (Blueprint $table) {
                $table->text('teacher_comment')->nullable()->after('passes');
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty: the column may have pre-existed this migration.
    }
};
