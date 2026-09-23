<?php

namespace App\Domains\Admissions\Models;

use App\Support\Enums\GuardianRelationship;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationGuardian extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'application_id',
        'first_name',
        'last_name',
        'relationship',
        'phone',
        'email',
        'occupation',
        'national_id',
        'address',
        'is_primary',
        'is_emergency',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_emergency' => 'boolean',
        ];
    }

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
        return GuardianRelationship::tryFrom($this->relationship)?->label() ?? ucfirst($this->relationship ?? '');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }
}
