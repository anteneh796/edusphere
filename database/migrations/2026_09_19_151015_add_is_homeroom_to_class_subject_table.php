<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_subject', function (Blueprint $table) {
            $table->boolean('is_homeroom')->default(false)
                ->after('position')
                ->comment('Designates the class/subject pairing the teacher manages as homeroom.');
        });
    }

    public function down(): void
    {
        Schema::table('class_subject', function (Blueprint $table) {
            $table->dropColumn('is_homeroom');
        });
    }
};
