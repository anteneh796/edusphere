<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_card_id')->constrained('report_cards')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();

            /* Snapshot of the paper that produced this subject result */
            $table->unsignedInteger('position')->default(0);
            $table->decimal('max_marks', 6, 2)->default(0);
            $table->decimal('pass_marks', 6, 2)->default(0);
            $table->decimal('weight', 6, 2)->nullable();

            $table->decimal('marks_obtained', 8, 2)->default(0);

            /* Derived on generation and stored permanently (spec #9/#10/#12) */
            $table->decimal('percentage', 6, 2)->nullable();
            $table->string('grade', 4)->nullable();
            $table->boolean('passes')->default(false);

            $table->timestamps();

            $table->unique(['report_card_id', 'subject_id'], 'report_card_items_card_subject_unique');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_items');
    }
};
