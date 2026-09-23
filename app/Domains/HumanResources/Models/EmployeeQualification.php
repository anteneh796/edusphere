<?php

namespace App\Domains\HumanResources\Models;

use App\Support\Enums\QualificationType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeQualification extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'institution',
        'awarded_on',
        'expires_on',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'awarded_on' => 'date',
            'expires_on' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function typeEnum(): ?QualificationType
    {
        return QualificationType::tryFrom($this->type);
    }

    public function typeLabel(): string
    {
        return $this->typeEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}