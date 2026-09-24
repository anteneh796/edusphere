<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->unique('source_inquiry_id', 'admission_applications_source_inquiry_unique');
        });
    }

    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropUnique('admission_applications_source_inquiry_unique');
        });
    }
};
