<?php

namespace Database\Seeders;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmployeeStatusHistory;
use App\Domains\HumanResources\Models\EmploymentContract;
use App\Domains\HumanResources\Models\HrDocument;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\LeaveType;
use App\Domains\HumanResources\Models\OfficialLetter;
use App\Domains\HumanResources\Models\PayrollProfile;
use App\Domains\HumanResources\Models\PerformanceReview;
use App\Domains\HumanResources\Models\Position;
use App\Domains\HumanResources\Models\StaffAttendance;
use App\Domains\HumanResources\Models\TrainingRecord;
use App\Domains\HumanResources\Services\EmployeeService;
use App\Support\Enums\ContractRenewalStatus;
use App\Support\Enums\DocumentVerificationStatus;
use App\Support\Enums\EmploymentStatus;
use App\Support\Enums\EmploymentType;
use App\Support\Enums\HrDocumentCategory;
use App\Support\Enums\LeaveRequestStatus;
use App\Support\Enums\OfficialLetterType;
use App\Support\Enums\PerformanceReviewStatus;
use App\Support\Enums\StaffAttendanceStatus;
use Illuminate\Database\Seeder;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDepartmentsAndPositions();
        $this->seedLeaveTypes();

        $hrOfficer = User::where('email', 'hr@edusphere.com')->first();

        $employees = $this->seedEmployees();
        $this->seedContracts($employees, $hrOfficer);
        $this->seedLeaveRequests($employees, $hrOfficer);
        $this->seedAttendance($employees, $hrOfficer);
        $this->seedPerformanceReviews($employees, $hrOfficer);
        $this->seedTrainingAndDocuments($employees, $hrOfficer);
        $this->seedPayroll($employees);
        $this->seedLetters($employees, $hrOfficer);
    }

    private function seedDepartmentsAndPositions(): void
    {
        $departments = [
            ['name' => 'Academics', 'code' => 'ACA', 'description' => 'Teaching, curriculum and exam administration.'],
            ['name' => 'Administration', 'code' => 'ADM', 'description' => 'School leadership and general administration.'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Budgeting, fees and payroll.'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Staff records, pay and welfare.'],
            ['name' => 'IT & Systems', 'code' => 'ITS', 'description' => 'Technology, data and digital systems.'],
            ['name' => 'Student Support', 'code' => 'SSU', 'description' => 'Guidance, library and wellbeing.'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Facilities, transport and security.'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                ['name' => $department['name'], 'description' => $department['description'], 'is_active' => true]
            );
        }

        $positions = [
            ['School Director', 'executive', 'Administration'],
            ['Vice Principal', 'executive', 'Administration'],
            ['School Administrator', 'administration', 'Administration'],
            ['HR Officer', 'hr', 'Human Resources'],
            ['Registrar', 'administration', 'Academics'],
            ['Receptionist', 'administration', 'Operations'],
            ['Mathematics Teacher', 'academic', 'Academics'],
            ['English Teacher', 'academic', 'Academics'],
            ['Science Teacher', 'academic', 'Academics'],
            ['ICT Officer', 'it', 'IT & Systems'],
            ['Accountant', 'finance', 'Finance'],
            ['Librarian', 'student_support', 'Student Support'],
        ];

        foreach ($positions as [$name, $category, $departmentCode]) {
            Position::updateOrCreate(
                ['name' => $name],
                [
                    'department_id' => Department::where('code', $departmentCode)->value('id'),
                    'category' => $category,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedLeaveTypes(): void
    {
        $leaveTypes = [
            ['Annual Leave', 'ANL', 24, true, 'Planned leave accrued per service year.'],
            ['Sick Leave', 'SL', 15, true, 'Paid leave for medical reasons.'],
            ['Maternity Leave', 'MAT', 90, true, 'Leave for childbirth and early childcare.'],
            ['Paternity Leave', 'PAT', 5, true, 'Short leave for new fathers.'],
            ['Study Leave', 'STD', 15, true, 'Leave for further studies and exams.'],
            ['Emergency Leave', 'EMG', 5, true, 'Unplanned leave for family emergencies.'],
            ['Unpaid Leave', 'UPL', 30, false, 'Leave without pay, at management discretion.'],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::updateOrCreate(
                ['code' => $leaveType[1]],
                [
                    'name' => $leaveType[0],
                    'days_per_year' => $leaveType[2],
                    'is_paid' => $leaveType[3],
                    'description' => $leaveType[4],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedEmployees(): \Illuminate\Support\Collection
    {
        $staff = [
            ['full_name' => 'Bereket Mengistu', 'email' => 'principal@edusphere.com', 'department' => 'Administration', 'position' => 'School Director', 'employee_id' => 'EMP-'.now()->format('y').'-0101'],
            ['full_name' => 'Mahlet Gebeyehu', 'email' => 'vice_principal@edusphere.com', 'department' => 'Administration', 'position' => 'Vice Principal', 'employee_id' => 'EMP-'.now()->format('y').'-0102'],
            ['full_name' => 'Tesfahun Deresse', 'email' => 'school_admin@edusphere.com', 'department' => 'Administration', 'position' => 'School Administrator', 'employee_id' => 'EMP-'.now()->format('y').'-0103'],
            ['full_name' => 'Lidya Yirga', 'email' => 'hr@edusphere.com', 'department' => 'Human Resources', 'position' => 'HR Officer', 'employee_id' => 'EMP-'.now()->format('y').'-0104'],
            ['full_name' => 'Hirut Lemma', 'email' => 'registrar@edusphere.com', 'department' => 'Academics', 'position' => 'Registrar', 'employee_id' => 'EMP-'.now()->format('y').'-0105'],
            ['full_name' => 'Selam Ayele', 'email' => 'reception@edusphere.com', 'department' => 'Operations', 'position' => 'Receptionist', 'employee_id' => 'EMP-'.now()->format('y').'-0106'],
            ['full_name' => 'Mulugeta Assefa', 'email' => 'teacher1@edusphere.com', 'department' => 'Academics', 'position' => 'Mathematics Teacher', 'employee_id' => 'EMP-'.now()->format('y').'-0107'],
            ['full_name' => 'Tigist Shiferaw', 'email' => 'teacher2@edusphere.com', 'department' => 'Academics', 'position' => 'English Teacher', 'employee_id' => 'EMP-'.now()->format('y').'-0108'],
            ['full_name' => 'Daniel Tamrat', 'email' => 'teacher3@edusphere.com', 'department' => 'Academics', 'position' => 'Science Teacher', 'employee_id' => 'EMP-'.now()->format('y').'-0109'],
            ['full_name' => 'Abel Kebede', 'email' => null, 'department' => 'IT & Systems', 'position' => 'ICT Officer', 'employee_id' => 'EMP-'.now()->format('y').'-0110'],
            ['full_name' => 'Marta Desta', 'email' => null, 'department' => 'Finance', 'position' => 'Accountant', 'employee_id' => 'EMP-'.now()->format('y').'-0111'],
            ['full_name' => 'Yonas Girma', 'email' => null, 'department' => 'Student Support', 'position' => 'Librarian', 'employee_id' => 'EMP-'.now()->format('y').'-0112'],
        ];

        $employees = collect();

        foreach ($staff as $index => $data) {
            $employee = app(EmployeeService::class)->create([
                'employee_id' => $data['employee_id'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'gender' => fake()->randomElement(['male', 'female']),
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-22 years')->format('Y-m-d'),
                'phone' => fake()->numerify('+2519########'),
                'address' => fake()->optional()->address(),
                'department_id' => Department::where('code', $this->departmentCode($data['department']))->value('id'),
                'position_id' => Position::where('name', $data['position'])->value('id'),
                'employment_type' => $index % 3 === 2 ? EmploymentType::Contract->value : EmploymentType::FullTime->value,
                'joining_date' => fake()->dateTimeBetween('-8 years', '-6 months')->format('Y-m-d'),
                'employment_status' => $index % 5 === 4 ? EmploymentStatus::Probation->value : EmploymentStatus::Active->value,
                'university' => fake()->optional()->company(),
                'qualification' => $index < 4 ? 'Masters Degree' : 'Bachelor Degree',
                'degree' => $index < 4 ? 'MA' : 'BA',
                'specialization' => fake()->optional()->word(),
                'teaching_license' => $index >= 6 && $index <= 8 ? 'TL-'.fake()->numerify('####') : null,
                'years_of_experience' => fake()->numberBetween(2, 22),
                'professional_skills' => fake()->optional()->sentence(6),
                'create_account' => false,
            ]);

            if ($data['email']) {
                $user = User::where('email', $data['email'])->first();
                $employee->forceFill(['user_id' => $user?->getKey()])->save();
            }

            $employees->push($employee);
        }

        // A couple of terminated/resigned records for history.
        $history = [
            ['full_name' => 'Hana Wolde', 'department' => 'Academics', 'position' => 'English Teacher', 'status' => EmploymentStatus::Resigned->value, 'employee_id' => 'EMP-'.now()->format('y').'-0113'],
            ['full_name' => 'Kaleb Tesfaye', 'department' => 'Operations', 'position' => 'Receptionist', 'status' => EmploymentStatus::Terminated->value, 'employee_id' => 'EMP-'.now()->format('y').'-0114'],
        ];

        foreach ($history as $data) {
            $employee = Employee::updateOrCreate(
                ['employee_id' => $data['employee_id']],
                [
                    'full_name' => $data['full_name'],
                    'department_id' => Department::where('code', $this->departmentCode($data['department']))->value('id'),
                    'position_id' => Position::where('name', $data['position'])->value('id'),
                    'employment_type' => EmploymentType::FullTime->value,
                    'joining_date' => now()->subYears(3)->toDateString(),
                    'employment_status' => $data['status'],
                ]
            );

            EmployeeStatusHistory::firstOrCreate(
                ['employee_id' => $employee->getKey(), 'new_status' => $data['status'], 'old_status' => EmploymentStatus::Active->value],
                ['changed_at' => now()->subMonths(3), 'notes' => 'Seeded demo record.']
            );
        }

        return $employees;
    }

    private function departmentCode(string $name): string
    {
        return match (true) {
            str_contains($name, 'Academ') => 'ACA',
            str_contains($name, 'Administ') => 'ADM',
            str_contains($name, 'Finance') => 'FIN',
            str_contains($name, 'Human Resources') => 'HR',
            str_contains($name, 'IT') => 'ITS',
            str_contains($name, 'Student Support') => 'SSU',
            default => 'OPS',
        };
    }

    private function seedContracts(\Illuminate\Support\Collection $employees, ?User $hrOfficer): void
    {
        foreach ($employees as $index => $employee) {
            $start = $employee->joining_date;

            EmploymentContract::firstOrCreate(
                ['employee_id' => $employee->getKey(), 'contract_number' => 'CT-'.now()->format('y').'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'employment_type' => $employee->employment_type,
                    'position_id' => $employee->position_id,
                    'department_id' => $employee->department_id,
                    'start_date' => $start,
                    'end_date' => $employee->employment_type === EmploymentType::Contract->value
                        ? now()->addMonth()->toDateString()
                        : now()->addYear()->toDateString(),
                    'salary_grade' => 'A'.($index % 4 + 1),
                    'basic_salary' => 12000 + ($index * 1500),
                    'working_hours_per_week' => 40,
                    'renewal_status' => ContractRenewalStatus::Active->value,
                    'notes' => null,
                ]
            );
        }
    }

    private function seedLeaveRequests(\Illuminate\Support\Collection $employees, ?User $hrOfficer): void
    {
        $annual = LeaveType::where('code', 'ANL')->first();
        $sick = LeaveType::where('code', 'SL')->first();

        if (! $annual || ! $sick) {
            return;
        }

        $reviewerId = $hrOfficer?->getKey();

        foreach ($employees->take(6) as $index => $employee) {
            $start = now()->startOfMonth()->addDays(3 + ($index * 3));
            $type = $index % 2 === 0 ? $annual : $sick;

            LeaveRequest::firstOrCreate(
                ['employee_id' => $employee->getKey(), 'start_date' => $start->toDateString()],
                [
                    'leave_type_id' => $type->getKey(),
                    'end_date' => $start->copy()->addDays(2)->toDateString(),
                    'days' => 3,
                    'reason' => 'Seeded approved leave for the demo period.',
                    'status' => LeaveRequestStatus::Approved->value,
                    'submitted_at' => $start->copy()->subDays(5),
                    'submitted_by_id' => $employee->user_id ?? $reviewerId,
                    'reviewed_by_id' => $reviewerId,
                    'reviewed_at' => now(),
                    'reviewed_note' => 'Approved by HR.',
                ]
            );
        }
    }

    private function seedAttendance(\Illuminate\Support\Collection $employees, ?User $hrOfficer): void
    {
        foreach ($employees->take(8) as $employee) {
            foreach ([1, 2, 3, 4, 5, 8, 9, 10] as $daysAgo) {
                $date = now()->subDays($daysAgo)->startOfDay();

                if ($date->isWeekend()) {
                    continue;
                }

                StaffAttendance::firstOrCreate(
                    ['employee_id' => $employee->getKey(), 'attendance_date' => $date->toDateString()],
                    [
                        'status' => fake()->randomElement([StaffAttendanceStatus::Present->value, StaffAttendanceStatus::Present->value, StaffAttendanceStatus::Late->value, StaffAttendanceStatus::OfficialDuty->value]),
                        'check_in' => $date->toDateString().' 08:10:00',
                        'check_out' => $date->toDateString().' 16:45:00',
                        'recorded_by_id' => $hrOfficer?->getKey(),
                    ]
                );
            }
        }
    }

    private function seedPerformanceReviews(\Illuminate\Support\Collection $employees, ?User $hrOfficer): void
    {
        foreach ($employees->take(3) as $employee) {
            $scores = collect([
                'teaching_quality' => fake()->numberBetween(60, 98),
                'classroom_management' => fake()->numberBetween(60, 98),
                'professional_conduct' => fake()->numberBetween(70, 99),
                'attendance_score' => fake()->numberBetween(70, 100),
                'student_engagement' => fake()->numberBetween(55, 95),
                'admin_responsibility' => fake()->numberBetween(55, 95),
            ]);

            PerformanceReview::firstOrCreate(
                ['employee_id' => $employee->getKey(), 'period' => 'Q2 '.now()->format('Y')],
                [
                    'evaluator_id' => $hrOfficer?->getKey(),
                    ...$scores->all(),
                    'overall_score' => round($scores->avg(), 2),
                    'strengths' => 'Consistent, reliable and team oriented.',
                    'improvements' => 'Continue developing assessment design skills.',
                    'recommendations' => 'Attend student-centred pedagogy training.',
                    'status' => PerformanceReviewStatus::Completed->value,
                    'reviewed_at' => now()->subWeek(),
                ]
            );
        }
    }

    private function seedTrainingAndDocuments(\Illuminate\Support\Collection $employees, ?User $hrOfficer): void
    {
        $courses = [
            ['Active Teaching Methodologies', 'Ministry of Education', 24],
            ['Safeguarding Children', 'ChildFund Ethiopia', 12],
            ['Digital Literacy for Teachers', 'ICT Academy', 16],
        ];

        foreach ($employees->take(5) as $employee) {
            $course = $courses[array_rand($courses)];

            TrainingRecord::firstOrCreate(
                ['employee_id' => $employee->getKey(), 'course_name' => $course[0]],
                [
                    'provider' => $course[1],
                    'trained_on' => now()->subMonths(2)->toDateString(),
                    'completed_on' => now()->subMonths(1)->toDateString(),
                    'hours' => $course[2],
                    'remarks' => 'Seeded demonstration record.',
                ]
            );
        }

        $categories = [HrDocumentCategory::DegreeCertificate, HrDocumentCategory::TeachingLicense, HrDocumentCategory::NationalId];

        foreach ($employees->take(6) as $employee) {
            $category = $categories[array_rand($categories)];

            HrDocument::firstOrCreate(
                ['employee_id' => $employee->getKey(), 'title' => $category->label()],
                [
                    'category' => $category->value,
                    'file_path' => 'demo/hr/'.$employee->employee_id.'-'.strtolower(str_replace('_', '-', $category->value)).'.pdf',
                    'uploaded_by_id' => $hrOfficer?->getKey(),
                    'uploaded_at' => now()->subMonth(),
                    'verification_status' => fake()->randomElement([DocumentVerificationStatus::Verified->value, DocumentVerificationStatus::Pending->value]),
                    'notes' => 'Seeded document for the demo environment.',
                ]
            );
        }
    }

    private function seedPayroll(\Illuminate\Support\Collection $employees): void
    {
        foreach ($employees->take(8) as $index => $employee) {
            PayrollProfile::firstOrCreate(
                ['employee_id' => $employee->getKey()],
                [
                    'salary_grade' => 'A'.($index % 4 + 1),
                    'basic_salary' => 12000 + ($index * 1500),
                    'allowances' => ['housing' => 1500, 'transport' => 800],
                    'bank_name' => fake()->randomElement(['Commercial Bank of Ethiopia', 'Dashen Bank', 'Awash Bank']),
                    'account_number' => fake()->numerify('##################'),
                    'tax_id' => fake()->numerify('##/######'),
                    'payment_method' => 'bank_transfer',
                ]
            );
        }
    }

    private function seedLetters(\Illuminate\Support\Collection $employees, ?User $hrOfficer): void
    {
        $templates = [
            [OfficialLetterType::Appointment, 'Appointment Letter'],
            [OfficialLetterType::ExperienceCertificate, 'Certificate of Experience'],
        ];

        foreach ($employees->take(2) as $index => $employee) {
            [$type, $title] = $templates[$index];

            OfficialLetter::firstOrCreate(
                ['reference_number' => OfficialLetter::nextReferenceNumber()],
                [
                    'employee_id' => $employee->getKey(),
                    'letter_type' => $type->value,
                    'title' => $title.' — '.$employee->full_name,
                    'content' => "This is to certify that ".$employee->full_name." served EduSphere with dedication and professionalism.\n\nThe appointment is effective as of ".$employee->joining_date->format('F j, Y').".\n\nWe wish them continued success.",
                    'issued_by_id' => $hrOfficer?->getKey(),
                    'issued_on' => now()->subDays(5)->toDateString(),
                ]
            );
        }
    }
}