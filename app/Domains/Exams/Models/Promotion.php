<?php

namespace App\Domains\Exams\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\PromotionStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'from_class_room_id',
        'to_class_room_id',
        'from_grade',
        'to_grade',
        'status',
        'remarks',
        'decided_by_id',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PromotionStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    /* ----------------------------- Relations ----------------------------- */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function fromClassRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'from_class_room_id');
    }

    public function toClassRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'to_class_room_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_id');
    }
}
