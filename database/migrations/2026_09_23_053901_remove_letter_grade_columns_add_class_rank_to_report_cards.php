<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EduSphere grading policy: Marks -> Total -> Average -> Rank.
     *
     * Letter grades (A/B/C/D/F) are removed entirely and report cards gain
     * a competition class rank computed from total marks.
     */
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->unsignedMediumInteger('class_rank')->nullable()->after('average_percent');
            $table->unsignedMediumInteger('class_size')->nullable()->after('class_rank');
            $table->dropColumn(['overall_grade', 'grade_summary']);
        });

        Schema::table('report_card_items', function (Blueprint $table) {
            $table->dropColumn('grade');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('grade');
        });

        Schema::table('assessment_results', function (Blueprint $table) {
            $table->dropColumn('grade');
        });

        Schema::table('transcripts', function (Blueprint $table) {
            $table->dropColumn('overall_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcripts', function (Blueprint $table) {
            $table->string('overall_grade', 4)->nullable();
        });

        Schema::table('assessment_results', function (Blueprint $table) {
            $table->string('grade', 5)->nullable();
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->string('grade', 4)->nullable();
        });

        Schema::table('report_card_items', function (Blueprint $table) {
            $table->string('grade', 4)->nullable();
        });

        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropColumn(['class_rank', 'class_size']);
            $table->string('overall_grade', 4)->nullable();
            $table->json('grade_summary')->nullable();
        });
    }
};
