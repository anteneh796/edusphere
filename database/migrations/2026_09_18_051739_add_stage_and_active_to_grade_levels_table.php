<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->string('stage', 30)->nullable()->after('code');
            $table->boolean('is_active')->default(true)->after('stage');
        });

        if (Schema::hasTable('grade_levels')) {
            DB::table('grade_levels')->orderBy('sort_order')->get()->each(function ($grade): void {
                $stage = match (true) {
                    $grade->code === 'KG1' || $grade->code === 'KG2' => 'kindergarten',
                    in_array($grade->code, ['1', '2', '3', '4'], true) => 'lower_primary',
                    in_array($grade->code, ['5', '6', '7', '8'], true) => 'upper_primary',
                    default => null,
                };

                DB::table('grade_levels')->where('id', $grade->id)->update([
                    'stage' => $stage,
                    'is_active' => $stage !== null ? 1 : 0,
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropColumn(['stage', 'is_active']);
        });
    }
};
