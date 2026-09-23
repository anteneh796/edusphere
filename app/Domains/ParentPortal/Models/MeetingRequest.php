<?php

namespace App\Domains\ParentPortal\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Support\Enums\MeetingRequestStatus;
use App\Support\Enums\MeetingRequestType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRequest extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'guardian_id',
        'student_id',
        'teacher_id',
        'meeting_type',
        'reason',
        'preferred_date',
        'preferred_time',
        'status',
        'staff_note',
        'requested_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'meeting_type' => MeetingRequestType::class,
            'status' => MeetingRequestStatus::class,
            'preferred_date' => 'date',
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
