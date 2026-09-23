<?php

namespace App\Domains\ParentPortal\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ParentRequestStatus;
use App\Support\Enums\ParentRequestType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentRequest extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'guardian_id',
        'student_id',
        'reference_number',
        'type',
        'subject',
        'description',
        'status',
        'assigned_to_id',
        'resolution',
        'staff_note',
        'submitted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ParentRequestType::class,
            'status' => ParentRequestStatus::class,
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
