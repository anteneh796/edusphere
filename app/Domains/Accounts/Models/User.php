<?php

namespace App\Domains\Accounts\Models;

use App\Support\Enums\RoleName;
use App\Support\Enums\UserStatus;
use App\Support\HasUuid;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'status',
        'avatar_path',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ---------------------------------- Helpers --------------------------------- */

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function initials(): string
    {
        $initials = str($this->first_name)->substr(0, 1)->value().
            str($this->last_name)->substr(0, 1)->value();

        return strtoupper($initials);
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active->value;
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
        ]);
    }

    /* -------------------------------- Relations -------------------------------- */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->roles()->with('permissions')->get()
            ->flatMap->permissions->unique('id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active->value);
    }
}
