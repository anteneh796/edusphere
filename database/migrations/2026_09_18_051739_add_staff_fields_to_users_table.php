<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_type', 30)->nullable()->after('phone');
            $table->string('department', 100)->nullable()->after('staff_type');
            $table->string('job_title', 120)->nullable()->after('department');
            $table->date('hire_date')->nullable()->after('job_title');
            $table->date('contract_end_date')->nullable()->after('hire_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['staff_type', 'department', 'job_title', 'hire_date', 'contract_end_date']);
        });
    }
};
