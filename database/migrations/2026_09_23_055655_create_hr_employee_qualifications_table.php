<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_qualifications')) {
            Schema::create('employee_qualifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
                $table->string('type', 20)->default('qualification');
                $table->string('title', 150);
                $table->string('institution', 150)->nullable();
                $table->date('awarded_on')->nullable();
                $table->date('expires_on')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'type']);
            });

            return;
        }

        // The table may already exist because an earlier migration run
        // created it before failing while adding its foreign key.
        Schema::table('employee_qualifications', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();
        });

        if (! Schema::hasIndex('employee_qualifications', ['employee_id', 'type'])) {
            Schema::table('employee_qualifications', function (Blueprint $table) {
                $table->index(['employee_id', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_qualifications');
    }
};
