<?php

namespace App\Domains\Academics\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'sort_order',
    ];

    /* -------------------------------- Relations -------------------------------- */

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class)
            ->using(ClassSubject::class)
            ->withPivot(['teacher_id', 'periods_per_week', 'position'])
            ->orderByPivot('position');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
