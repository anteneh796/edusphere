<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\AttendanceCorrectionStatus;
use App\Support\Enums\AttendanceStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'attendance_record_id',
        'requested_by_id',
        'reviewed_by_id',
        'requested_status',
        'old_status',
        'new_status',
        'status',
        'reason',
        'reviewer_note',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_status' => AttendanceStatus::class,
            'old_status' => AttendanceStatus::class,
            'new_status' => AttendanceStatus::class,
            'status' => AttendanceCorrectionStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === AttendanceCorrectionStatus::Pending;
    }

    public function scopePending($query): void
    {
        $query->where('status', AttendanceCorrectionStatus::Pending->value);
    }

    /* -------------------------------- Relations -------------------------------- */

    public function record(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }
}
