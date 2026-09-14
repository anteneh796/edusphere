<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AttendanceStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'status',
        'note',
        'marked_by_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
        ];
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '';
    }

    public function statusBadgeColor(): string
    {
        return $this->status?->badgeColor() ?? 'neutral';
    }

    /* -------------------------------- Relations -------------------------------- */

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by_id');
    }
}
