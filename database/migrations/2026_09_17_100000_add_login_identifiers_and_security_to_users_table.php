<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('email');
            $table->string('employee_id', 30)->nullable()->unique()->after('username');
            $table->string('student_number', 30)->nullable()->unique()->after('employee_id');
            $table->timestamp('password_changed_at')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('password_changed_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('must_change_password')->default(false)->after('last_login_ip');
        });

        $now = now();
        DB::table('users')->whereNull('password_changed_at')->update(['password_changed_at' => $now]);

        $takenUsernames = DB::table('users')->whereNotNull('username')->pluck('username')->all();
        $takenUsernames = array_flip($takenUsernames);

        $sequence = 0;
        foreach (DB::table('users')->whereNull('username')->orderBy('created_at')->get() as $user) {
            $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $user->first_name).'.'
                .preg_replace('/[^a-z0-9]/i', '', $user->last_name));
            $candidate = $base;
            while (isset($takenUsernames[$candidate])) {
                $candidate = $base.++$sequence;
            }
            $takenUsernames[$candidate] = true;
            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
        }

        $sequence = 1;
        $takenEmployeeIds = DB::table('users')->whereNotNull('employee_id')->pluck('employee_id')->all();
        $takenEmployeeIds = array_flip($takenEmployeeIds);
        foreach (DB::table('users')->whereNull('employee_id')->orderBy('created_at')->get() as $user) {
            $candidate = 'EMP-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            while (isset($takenEmployeeIds[$candidate])) {
                $candidate = 'EMP-'.str_pad((string) (++$sequence), 4, '0', STR_PAD_LEFT);
            }
            $takenEmployeeIds[$candidate] = true;
            DB::table('users')->where('id', $user->id)->update(['employee_id' => $candidate]);
        }

        if (Schema::hasTable('students')) {
            $studentNumbers = DB::table('students')->whereNotNull('user_id')->whereNotNull('student_number')->get(['user_id', 'student_number']);
            foreach ($studentNumbers as $row) {
                DB::table('users')
                    ->where('id', $row->user_id)
                    ->whereNull('student_number')
                    ->update(['student_number' => $row->student_number]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['employee_id']);
            $table->dropUnique(['student_number']);
            $table->dropColumn([
                'username',
                'employee_id',
                'student_number',
                'password_changed_at',
                'last_login_at',
                'last_login_ip',
                'must_change_password',
            ]);
        });
    }
};
