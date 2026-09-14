<?php

namespace App\Domains\Students\Services;

use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;

class GuardianService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Guardian::query()
            ->withCount('students')
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): Guardian
    {
        $studentIds = $data['student_ids'] ?? [];
        unset($data['student_ids']);

        $guardian = Guardian::create($data);

        foreach ($studentIds as $studentId) {
            $guardian->students()->attach($studentId, ['is_primary' => $this->isPrimaryFor($guardian, $studentId)]);
        }

        return $guardian->loadCount('students');
    }

    public function update(Guardian $guardian, array $data): Guardian
    {
        $studentIds = $data['student_ids'] ?? null;
        unset($data['student_ids']);

        $guardian->update($data);

        if (is_array($studentIds)) {
            $existing = $guardian->students()->pluck('students.id')->all();
            $removed = array_values(array_diff($existing, $studentIds));
            $added = array_values(array_diff($studentIds, $existing));

            if ($removed !== []) {
                $guardian->students()->detach($removed);
            }

            foreach ($added as $studentId) {
                $guardian->students()->attach($studentId, ['is_primary' => $this->isPrimaryFor($guardian, $studentId)]);
            }
        }

        return $guardian->loadCount('students');
    }

    public function delete(Guardian $guardian): void
    {
        $guardian->students()->detach();
        Student::where('guardian_id', $guardian->getKey())->update(['guardian_id' => null]);
        $guardian->delete();
    }

    private function isPrimaryFor(Guardian $guardian, string $studentId): bool
    {
        return Student::whereKey($studentId)->value('guardian_id') === $guardian->getKey();
    }
}
