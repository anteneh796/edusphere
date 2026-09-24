<?php

namespace App\Domains\Students\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\ParentRelationship;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const ACCESS = ['academics', 'attendance', 'messages', 'documents', 'requests'];

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'relationship',
        'phone',
        'email',
        'occupation',
        'national_id',
        'address',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function initials(): string
    {
        return strtoupper(
            str($this->first_name)->substr(0, 1)->value().
            str($this->last_name)->substr(0, 1)->value()
        );
    }

    public function relationshipLabel(): string
    {
        return ParentRelationship::tryFrom($this->relationship)?->label() ?? ucfirst($this->relationship ?? '');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->using(GuardianStudentPivot::class)
            ->withTimestamps()
            ->withPivot('is_primary', 'permissions');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* --------------------------- Relationship permissions ------------------------- */

    public static function defaultPermissions(): array
    {
        return array_combine(self::ACCESS, array_fill(0, count(self::ACCESS), true));
    }

    public function permissionsFor(Student $student): array
    {
        $pivot = $this->students()->whereKey($student->getKey())->first();

        return ($pivot?->pivot?->permissions ?? []) + self::defaultPermissions();
    }

    public function canAccess(string $access, Student $student): bool
    {
        return in_array($access, self::ACCESS, true)
            && ($this->permissionsFor($student)[$access] ?? false);
    }
}
