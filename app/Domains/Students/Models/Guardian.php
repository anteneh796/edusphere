<?php

namespace App\Domains\Students\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\GuardianRelationship;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

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
        return GuardianRelationship::tryFrom($this->relationship)?->label() ?? ucfirst($this->relationship ?? '');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)->withTimestamps()->withPivot('is_primary');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
