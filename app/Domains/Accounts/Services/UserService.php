<?php

namespace App\Domains\Accounts\Services;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->whereHas('roles', fn ($q) => $q->where('roles.name', $role));
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function roles(): Collection
    {
        return Role::orderBy('label')->get();
    }

    public function create(array $data, array $roleIds): User
    {
        $user = User::create(Arr::except($data, ['roles', 'password_confirmation']));
        $user->roles()->sync($roleIds);

        return $user->load('roles');
    }

    public function update(User $user, array $data, array $roleIds): User
    {
        if (! empty($data['password'])) {
            $password = $data['password'];
            unset($data['password']);
            PasswordService::recordHistory($user, $password);
        }

        $user->update(Arr::except($data, ['roles', 'password_confirmation']));
        $user->roles()->sync($roleIds);

        return $user->load('roles');
    }

    /**
     * Administrator-triggered password reset. Stores the new password in the
     * user's history, forces a change at next sign-in and kills other sessions.
     */
    public function resetPassword(User $user, ?string $password = null): User
    {
        $password ??= Str::password(12);

        PasswordService::recordHistory($user, $password);

        $user->forceFill(['must_change_password' => true])->saveQuietly();

        DB::table('sessions')
            ->where('user_id', $user->getKey())
            ->where('id', '!=', session()->getId())
            ->delete();

        ActivityLogger::log('admin reset password for '.$user->full_name, 'users', $user->id);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->roles()->detach();
        $user->delete();
    }
}
