<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_card_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_id')->constrained('report_cards')->cascadeOnDelete();

            /* ReportCardCommentType enum: teacher / principal / attendance */
            $table->string('type', 20)->default('teacher');

            $table->string('comment', 1000);
            $table->tinyInteger('position')->default(0);

            $table->foreignUuid('commented_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['report_card_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_comments');
    }
};
