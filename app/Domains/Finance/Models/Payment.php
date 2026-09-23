<?php

namespace App\Domains\Finance\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\PaymentMethod;
use App\Support\Enums\PaymentStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'student_id',
        'invoice_id',
        'payment_number',
        'amount',
        'method',
        'status',
        'reference',
        'provider',
        'provider_reference',
        'paid_at',
        'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function confirm(): void
    {
        $this->forceFill([
            'status' => PaymentStatus::Confirmed,
            'paid_at' => $this->paid_at ?? now(),
        ])->saveQuietly();

        $this->invoice?->refreshStatus();
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Confirmed->value);
    }
}
