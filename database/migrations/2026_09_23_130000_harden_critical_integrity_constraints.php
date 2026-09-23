<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce application-level invariants at the database boundary as well.
     */
    public function up(): void
    {
        if (! Schema::hasIndex('attendance_sessions', ['class_room_id', 'date'], 'unique')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->unique(
                    ['class_room_id', 'date'],
                    'attendance_sessions_class_date_unique'
                );
            });
        }

        if (! Schema::hasIndex('attendance_records', ['attendance_session_id', 'student_id'], 'unique')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->unique(
                    ['attendance_session_id', 'student_id'],
                    'attendance_records_session_student_unique'
                );
            });
        }

        if (! Schema::hasIndex('student_enrollments', ['student_id', 'academic_year_id'], 'unique')) {
            Schema::table('student_enrollments', function (Blueprint $table) {
                $table->unique(
                    ['student_id', 'academic_year_id'],
                    'student_enrollments_student_year_unique'
                );
            });
        }

        if (! Schema::hasIndex('student_enrollments', ['class_room_id', 'academic_year_id', 'roll_number'], 'unique')) {
            Schema::table('student_enrollments', function (Blueprint $table) {
                $table->unique(
                    ['class_room_id', 'academic_year_id', 'roll_number'],
                    'student_enrollments_class_year_roll_unique'
                );
            });
        }

        if (! Schema::hasIndex('report_cards', ['exam_id', 'student_id'], 'unique')) {
            Schema::table('report_cards', function (Blueprint $table) {
                $table->unique(
                    ['exam_id', 'student_id'],
                    'report_cards_exam_student_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropUnique('report_cards_exam_student_unique');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropUnique('student_enrollments_student_year_unique');
            $table->dropUnique('student_enrollments_class_year_roll_unique');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('attendance_records_session_student_unique');
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropUnique('attendance_sessions_class_date_unique');
        });
    }
};
