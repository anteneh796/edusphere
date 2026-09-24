<?php

namespace App\Domains\Students\Models;

use App\Support\Enums\ParentRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentProfile extends Guardian
{
    protected $table = 'guardians';

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student', 'guardian_id', 'student_id')
            ->using(GuardianStudentPivot::class)
            ->withTimestamps()
            ->withPivot('is_primary', 'permissions');
    }

    public function relationshipLabel(): string
    {
        return ParentRelationship::tryFrom($this->relationship)?->label()
            ?? ucfirst($this->relationship ?? '');
    }
}
