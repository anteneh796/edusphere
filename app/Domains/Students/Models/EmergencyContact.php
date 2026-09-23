<?php

namespace App\Domains\Students\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'student_emergency_contacts';

    protected $fillable = [
        'student_id',
        'name',
        'relationship',
        'phone',
        'priority',
        'authorized_pickup',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'authorized_pickup' => 'boolean',
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
