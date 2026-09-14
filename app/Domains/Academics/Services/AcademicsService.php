<?php

namespace App\Domains\Academics\Services;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Support\Collection;

class AcademicsService
{
    public function currentYear(): AcademicYear
    {
        return AcademicYear::current()->orderBy('start_date')->first()
            ?? throw new \RuntimeException('No current academic year is configured.');
    }

    public function teachers(): Collection
    {
        return User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
    }

    public function assignSubjects(ClassRoom $class, array $rows): void
    {
        $submitted = [];

        foreach ($rows as $position => $row) {
            if (empty($row['subject_id'])) {
                continue;
            }

            $submitted[] = $row['subject_id'];

            ClassSubject::updateOrCreate(
                ['class_room_id' => $class->getKey(), 'subject_id' => $row['subject_id']],
                [
                    'teacher_id' => ! empty($row['teacher_id']) ? $row['teacher_id'] : null,
                    'periods_per_week' => ! empty($row['periods_per_week']) ? (int) $row['periods_per_week'] : null,
                    'position' => $position,
                ]
            );
        }

        $class->assignments()
            ->whereNotIn('subject_id', $submitted)
            ->delete();
    }
}
