<?php

namespace App\Domains\Finance\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\InvoiceStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'student_id',
        'invoice_number',
        'description',
        'amount',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function paidAmount(): float
    {
        return round((float) $this->payments()->confirmed()->sum('amount'), 2);
    }

    public function balance(): float
    {
        return round(max(0, (float) $this->amount - $this->paidAmount()), 2);
    }

    public function refreshStatus(): void
    {
        $balance = $this->balance();

        $status = match (true) {
            $balance <= 0 => InvoiceStatus::Paid,
            $balance < (float) $this->amount => InvoiceStatus::Partial,
            $this->due_date?->isPast() ?? false => InvoiceStatus::Overdue,
            default => InvoiceStatus::Pending,
        };

        $this->forceFill(['status' => $status])->saveQuietly();
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::Paid->value,
            InvoiceStatus::Partial->value,
        ]);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::Pending->value,
            InvoiceStatus::Partial->value,
            InvoiceStatus::Overdue->value,
        ]);
    }
}
