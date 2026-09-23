<?php

namespace App\Domains\Students\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMedicalRecord extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'blood_group',
        'allergies',
        'medical_conditions',
        'medications',
        'disability_support',
        'doctor_name',
        'emergency_hospital',
        'health_notes',
        'updated_by_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
