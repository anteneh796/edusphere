<?php

namespace App\Domains\HumanResources\Models;

use App\Support\Enums\ContractRenewalStatus;
use App\Support\Enums\EmploymentType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentContract extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'contract_number',
        'employment_type',
        'position_id',
        'department_id',
        'start_date',
        'end_date',
        'salary_grade',
        'basic_salary',
        'working_hours_per_week',
        'renewal_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'basic_salary' => 'decimal:2',
            'working_hours_per_week' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function typeEnum(): ?EmploymentType
    {
        return EmploymentType::tryFrom($this->employment_type);
    }

    public function typeLabel(): string
    {
        return $this->typeEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->employment_type));
    }

    public function renewalEnum(): ?ContractRenewalStatus
    {
        return ContractRenewalStatus::tryFrom($this->renewal_status);
    }

    public function renewalBadgeColor(): string
    {
        return $this->renewalEnum()?->badgeColor() ?? 'neutral';
    }

    public function renewalLabel(): string
    {
        return $this->renewalEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->renewal_status));
    }

    public function daysUntilExpiry(): int
    {
        if (! $this->end_date) {
            return PHP_INT_MAX;
        }

        return (int) now()->startOfDay()->diffInDays($this->end_date, false);
    }

    public function isExpiringWithin(int $days = 90): bool
    {
        $until = $this->daysUntilExpiry();

        return $until >= 0 && $until <= $days;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('renewal_status', ContractRenewalStatus::Active->value);
    }

    public function scopeExpiringWithin(Builder $query, int $days = 90): Builder
    {
        return $query
            ->whereNotNull('end_date')
            ->where('end_date', '>=', now()->startOfDay())
            ->where('end_date', '<=', now()->addDays($days)->endOfDay());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('end_date')->where('end_date', '<', now()->startOfDay());
    }
}