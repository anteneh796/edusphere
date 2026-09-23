<?php

namespace App\Domains\Students\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTimeline extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'type',
        'description',
        'event_date',
        'meta',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
