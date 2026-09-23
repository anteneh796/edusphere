<?php

namespace App\Domains\Students\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GuardianStudentPivot extends Pivot
{
    protected $table = 'guardian_student';

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'permissions' => 'array',
        ];
    }
}
