<?php

namespace App\Domains\Accounts\Models;

use App\Domains\Notifications\Models\Notification;
use App\Domains\Settings\Models\Setting;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Support\Enums\RoleName;
use App\Support\Enums\UserStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'employee_id',
        'student_number',
        'phone',
        'staff_type',
        'department',
        'job_title',
        'hire_date',
        'contract_end_date',
        'password',
        'status',
        'avatar_path',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'password_changed_at',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
            'hire_date' => 'date',
            'contract_end_date' => 'date',
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

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleName::SuperAdmin->value);
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
            RoleName::SchoolAdmin->value,
            RoleName::Principal->value,
        ]);
    }

    /**
     * Permission names inherited through the user's roles.
     */
    public function permissionNames(): Collection
    {
        return $this->roles()->with('permissions:id,name')->get()
            ->flatMap->permissions
            ->pluck('name')
            ->unique()
            ->values();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissionNames()->contains($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $owned = $this->permissionNames();

        return collect($permissions)->contains(fn (string $permission) => $owned->contains($permission));
    }

    /**
     * Whether the user must pick a new password before using the system.
     * Either forced by an administrator, or triggered by policy expiry.
     */
    public function requiresPasswordChange(?int $expirationDays = null): bool
    {
        if ($this->must_change_password ?? false) {
            return true;
        }

        $days = $expirationDays ?? (int) Setting::value('password_expiration_days', 0);

        $changedAt = $this->password_changed_at ?? null;

        if ($days <= 0 || ! $changedAt) {
            return false;
        }

        return $changedAt->lte(now()->subDays($days));
    }

    /* -------------------------------- Relations -------------------------------- */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }

    public function userNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /* ---------------------------------- Helpers --------------------------------- */

    /**
     * Whether this user is part of the school staff (any non-student/parent role).
     */
    public function isStaff(): bool
    {
        if ($this->hasRole([RoleName::Student->value, RoleName::Parent->value])) {
            return false;
        }

        return $this->roles()->exists();
    }

    public function isStaffType(string $staffType): bool
    {
        return $this->staff_type === $staffType;
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active->value);
    }
}
