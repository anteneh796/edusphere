<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_guardians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('relationship', 20);
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('national_id', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_emergency')->default(false);
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_guardians');
    }
};
