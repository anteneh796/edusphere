<?php

namespace App\Domains\Academics\Models;

use App\Domains\Students\Models\Student;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'grade_level_id',
        'academic_year_id',
        'name',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class)
            ->using(ClassSubject::class)
            ->withPivot(['teacher_id', 'periods_per_week', 'position'])
            ->orderByPivot('position');
    }

    public function assignments()
    {
        return $this->hasMany(ClassSubject::class);
    }
}
