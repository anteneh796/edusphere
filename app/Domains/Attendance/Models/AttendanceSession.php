<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\AttendanceSessionStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'class_room_id',
        'academic_year_id',
        'taken_by_id',
        'date',
        'status',
        'note',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AttendanceSessionStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        return $this->status === AttendanceSessionStatus::Open;
    }

    public function summaryLabel(): string
    {
        $counts = $this->records_count ?? $this->records()->count();

        return "{$counts} marked";
    }

    /* -------------------------------- Relations -------------------------------- */

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', AttendanceSessionStatus::Open->value);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }
}
