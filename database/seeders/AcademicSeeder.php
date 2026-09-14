<?php

namespace Database\Seeders;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedYears();
        $this->seedGrades();
        $this->seedSubjects();
        $this->seedClasses();
    }

    private function seedYears(): void
    {
        $years = [
            ['name' => '2023/24', 'start_date' => '2023-09-11', 'end_date' => '2024-07-05', 'is_current' => false],
            ['name' => '2024/25', 'start_date' => '2024-09-10', 'end_date' => '2025-07-04', 'is_current' => false],
            ['name' => '2025/26', 'start_date' => '2025-09-10', 'end_date' => '2026-07-03', 'is_current' => false],
            ['name' => '2026/27', 'start_date' => '2026-09-10', 'end_date' => '2027-07-02', 'is_current' => true],
            ['name' => '2027/28', 'start_date' => '2027-09-10', 'end_date' => '2028-07-07', 'is_current' => false],
        ];

        foreach ($years as $year) {
            AcademicYear::create($year);
        }
    }

    private function seedGrades(): void
    {
        $levels = [
            'KG1' => 'Kindergarten 1',
            'KG2' => 'Kindergarten 2',
            '1' => 'Grade 1',
            '2' => 'Grade 2',
            '3' => 'Grade 3',
            '4' => 'Grade 4',
            '5' => 'Grade 5',
            '6' => 'Grade 6',
            '7' => 'Grade 7',
            '8' => 'Grade 8',
            '9' => 'Grade 9',
            '10' => 'Grade 10',
            '11' => 'Grade 11',
            '12' => 'Grade 12',
        ];

        $order = 0;
        foreach ($levels as $code => $name) {
            GradeLevel::create([
                'name' => $name,
                'code' => $code,
                'sort_order' => ++$order,
            ]);
        }
    }

    private function seedSubjects(): void
    {
        $subjects = [
            ['Mathematics', 'MATH', 'Arithmetic, algebra, geometry and statistics.'],
            ['English', 'ENG', 'English language, reading and writing.'],
            ['Amharic', 'AMH', 'National language and literature.'],
            ['General Science', 'GSCI', 'Integrated physical and life science.'],
            ['Social Studies', 'SSTD', 'Citizenship, geography and history for lower grades.'],
            ['Environmental Science', 'ENV', 'Environment, health and agriculture basics.'],
            ['Physics', 'PHY', 'Mechanics, waves, electricity and modern physics.'],
            ['Chemistry', 'CHEM', 'Matter, reactions and the periodic table.'],
            ['Biology', 'BIO', 'Cells, life processes and ecology.'],
            ['History', 'HIST', 'Ethiopian and world history.'],
            ['Geography', 'GEOG', 'Physical and human geography.'],
            ['Civics & Ethical Education', 'CIVIC', 'Citizenship, rule of law and ethics.'],
            ['Information Technology', 'ICT', 'Computer literacy and programming basics.'],
            ['Physical Education', 'PE', 'Sport, fitness and health.'],
            ['Art', 'ART', 'Drawing, painting and creative expression.'],
            ['Music', 'MUS', 'Singing, instruments and music theory.'],
            ['Home Economics', 'HOME', 'Household management and life skills.'],
            ['Health Science', 'HLTH', 'Personal and community health.'],
        ];

        $order = 0;
        foreach ($subjects as $i => [$name, $code, $description]) {
            Subject::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'description' => $description, 'sort_order' => ++$order]
            );
        }
    }

    private function seedClasses(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $grades = GradeLevel::ordered()->get();

        foreach ($grades as $grade) {
            $code = $grade->code;
            $sections = in_array($code, ['KG1', 'KG2', '1', '2', '3', '4', '5', '6', '7', '8'], true) ? ['A', 'B'] : ['A'];

            foreach ($sections as $section) {
                ClassRoom::updateOrCreate(
                    ['grade_level_id' => $grade->getKey(), 'academic_year_id' => $currentYear->getKey(), 'name' => $code.' '.$section],
                    ['capacity' => 40]
                );
            }
        }
    }
}
