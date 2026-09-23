<?php

namespace App\Domains\Students\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\StudentTransferType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransfer extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'type',
        'from_class_room_id',
        'to_class_room_id',
        'transfer_date',
        'destination_school',
        'reason',
        'certificate_number',
        'approved_by_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
        ];
    }

    public function typeLabel(): string
    {
        return StudentTransferType::tryFrom($this->type)?->label() ?? ucfirst($this->type);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClassRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'from_class_room_id');
    }

    public function toClassRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'to_class_room_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
