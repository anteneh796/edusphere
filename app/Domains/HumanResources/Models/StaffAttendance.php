<?php

namespace App\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\StaffAttendanceStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'staff_attendance';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'status',
        'check_in',
        'check_out',
        'remarks',
        'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in' => 'datetime:H:i',
            'check_out' => 'datetime:H:i',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function statusEnum(): ?StaffAttendanceStatus
    {
        return StaffAttendanceStatus::tryFrom($this->status);
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label() ?? ucfirst($this->status);
    }

    public function statusBadgeColor(): string
    {
        return $this->statusEnum()?->badgeColor() ?? 'neutral';
    }
}