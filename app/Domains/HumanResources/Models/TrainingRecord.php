<?php

namespace App\Domains\HumanResources\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingRecord extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'course_name',
        'provider',
        'trained_on',
        'completed_on',
        'certificate_path',
        'hours',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'trained_on' => 'date',
            'completed_on' => 'date',
            'hours' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}