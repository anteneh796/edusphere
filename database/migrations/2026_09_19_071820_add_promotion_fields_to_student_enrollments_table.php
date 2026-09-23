<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->integer('roll_number')->nullable()->after('class_room_id');
            $table->string('result', 30)->nullable()->after('status');

            $table->unique(['academic_year_id', 'class_room_id', 'roll_number'], 'enroll_year_class_roll_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropUnique('enroll_year_class_roll_unique');
            $table->dropColumn(['roll_number', 'result']);
        });
    }
};
