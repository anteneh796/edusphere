<?php

namespace App\Domains\HumanResources\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollProfile extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'salary_grade',
        'basic_salary',
        'allowances',
        'bank_name',
        'account_number',
        'tax_id',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowances' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function totalEarnings(): float
    {
        $allowances = array_sum((array) $this->allowances);

        return round((float) $this->basic_salary + $allowances, 2);
    }
}