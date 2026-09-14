<?php

namespace Database\Seeders;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@edusphere.com');
        $password = env('ADMIN_PASSWORD', 'Admin@2026');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'phone' => null,
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('name', RoleName::SuperAdmin->value)->first();
        if ($role && ! $admin->roles()->where('role_id', $role->getKey())->exists()) {
            $admin->roles()->attach($role);
        }
    }
}
