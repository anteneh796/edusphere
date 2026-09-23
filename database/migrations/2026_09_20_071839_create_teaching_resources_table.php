<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('type', 20)->default('document'); // document|worksheet|presentation|video|link|image
            $table->string('resource_path', 255)->nullable();
            $table->string('external_url', 255)->nullable();
            $table->string('unit', 150)->nullable();
            $table->string('visibility', 20)->default('teacher'); // teacher|subject|school
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'class_subject_id']);
            $table->index(['subject_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_resources');
    }
};
