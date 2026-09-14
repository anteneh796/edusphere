<?php

namespace App\Domains\Accounts\Services;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

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
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update(Arr::except($data, ['roles', 'password_confirmation']));
        $user->roles()->sync($roleIds);

        return $user->load('roles');
    }

    public function delete(User $user): void
    {
        $user->roles()->detach();
        $user->delete();
    }
}
