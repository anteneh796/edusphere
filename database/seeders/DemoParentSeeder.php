<?php

namespace Database\Seeders;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\ParentPortal\Models\AbsenceRequest;
use App\Domains\ParentPortal\Models\MeetingRequest;
use App\Domains\ParentPortal\Models\ParentRequest;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\TeacherPortal\Models\TeacherMessage;
use App\Support\Enums\AbsenceRequestStatus;
use App\Support\Enums\MeetingRequestStatus;
use App\Support\Enums\MeetingRequestType;
use App\Support\Enums\ParentRequestStatus;
use App\Support\Enums\ParentRequestType;
use App\Support\Enums\PaymentMethod;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\RoleName;
use Illuminate\Database\Seeder;

class DemoParentSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $role = Role::where('name', RoleName::Parent->value)->first();

        if (! $currentYear || ! $role) {
            return;
        }

        if (! $currentYear->classRooms()->where('name', '5 A')->exists()) {
            $currentYear = AcademicYear::query()
                ->whereHas('classRooms', fn ($query) => $query->where('name', '5 A')->whereHas('students'))
                ->latest('start_date')
                ->first();
        }

        if (! $currentYear) {
            return;
        }

        $classA = ClassRoom::where('academic_year_id', $currentYear->getKey())
            ->where('name', '5 A')
            ->first();

        $classB = ClassRoom::where('academic_year_id', $currentYear->getKey())
            ->where('name', '6 A')
            ->first();

        $classC = ClassRoom::where('academic_year_id', $currentYear->getKey())
            ->where('name', '7 A')
            ->first();

        if (! $classA) {
            return;
        }

        $wardIn5A = $this->pickStudent($classA);
        $wardIn6A = $this->pickStudent($classB);
        $wardIn7A = $this->pickStudent($classC);

        $parent1 = $this->createParent('demo.parent1@edusphere.com', 'Tigist', 'Tadesse');
        $parent2 = $this->createParent('demo.parent2@edusphere.com', 'Biniyam', 'Desalegn');

        $guardian1 = $this->createGuardian($parent1, 'Tigist', 'Tadesse', 'mother');
        $guardian2 = $this->createGuardian($parent2, 'Biniyam', 'Desalegn', 'father');

        $this->linkWard($guardian1, $wardIn5A);
        $this->linkWard($guardian1, $wardIn6A);

        if ($wardIn7A) {
            $this->linkWard($guardian2, $wardIn7A, finance: false);
        }

        NotificationPreference::updateOrCreate(
            ['user_id' => $parent1->getKey()],
            ['notify_email' => true, 'notify_sms' => true, 'notify_push' => true]
        );

        Notification::firstOrCreate(
            ['user_id' => $parent1->getKey(), 'type' => 'welcome'],
            [
                'title' => __('Welcome to the parent portal'),
                'body' => __('You can now track attendance, grades, homework and fees for your children.'),
                'category' => 'system',
                'priority' => 'low',
                'icon' => 'bell',
                'redirect_url' => route('cms.parent.dashboard'),
            ]
        );

        if ($wardIn5A) {
            $this->seedFinance($wardIn5A);
            $this->seedRequests($guardian1, $wardIn5A);
            $this->seedMessage($parent1, $guardian1, $wardIn5A);
        }
    }

    private function pickStudent(?ClassRoom $class): ?Student
    {
        if (! $class) {
            return null;
        }

        return Student::query()->where('class_room_id', $class->getKey())->first();
    }

    private function createParent(string $email, string $first, string $last): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $first,
                'last_name' => $last,
                'password' => 'Dev@2026',
                'status' => 'active',
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('name', RoleName::Parent->value)->first();

        if ($role && ! $user->roles()->where('role_id', $role->getKey())->exists()) {
            $user->roles()->attach($role);
        }

        return $user;
    }

    private function createGuardian(User $user, string $first, string $last, string $relationship): Guardian
    {
        $guardian = Guardian::updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'first_name' => $first,
                'last_name' => $last,
                'relationship' => $relationship,
                'phone' => '+251911100001',
                'email' => $user->email,
            ]
        );

        return $guardian;
    }

    private function linkWard(Guardian $guardian, ?Student $student, bool $finance = true): void
    {
        if (! $student) {
            return;
        }

        if ($guardian->students()->whereKey($student->getKey())->exists()) {
            return;
        }

        $permissions = array_merge(Guardian::defaultPermissions(), ['finance' => $finance]);

        $guardian->students()->attach($student, [
            'is_primary' => true,
            'permissions' => $permissions,
        ]);
    }

    private function seedFinance(Student $student): void
    {
        $invoice = Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-DEMO1'],
            [
                'student_id' => $student->getKey(),
                'description' => 'Term 1 tuition fees',
                'amount' => 2500.00,
                'status' => 'partial',
                'issue_date' => now()->startOfMonth()->format('Y-m-d'),
                'due_date' => now()->addMonth()->format('Y-m-d'),
            ]
        );

        Payment::updateOrCreate(
            ['payment_number' => 'PAY-2026-DEMO1'],
            [
                'student_id' => $student->getKey(),
                'invoice_id' => $invoice->getKey(),
                'amount' => 1500.00,
                'method' => PaymentMethod::Mobile->value,
                'status' => PaymentStatus::Confirmed->value,
                'reference' => 'TX-DEMO-1',
                'provider' => 'DemoBank',
                'paid_at' => now()->subDays(5)->format('Y-m-d H:i:s'),
            ]
        );
    }

    private function seedRequests(Guardian $guardian, Student $student): void
    {
        AbsenceRequest::updateOrCreate(
            ['guardian_id' => $guardian->getKey(), 'student_id' => $student->getKey(), 'absence_date' => now()->addDays(10)->format('Y-m-d')],
            [
                'reason' => 'Medical check-up appointment',
                'status' => AbsenceRequestStatus::Submitted->value,
                'submitted_at' => now(),
            ]
        );

        $teacher = $student->homeroomTeacher();

        if ($teacher) {
            MeetingRequest::updateOrCreate(
                ['guardian_id' => $guardian->getKey(), 'student_id' => $student->getKey(), 'teacher_id' => $teacher->getKey()],
                [
                    'meeting_type' => MeetingRequestType::InPerson->value,
                    'reason' => 'Discuss academic progress',
                    'preferred_date' => now()->addDays(14)->format('Y-m-d'),
                    'preferred_time' => '15:30',
                    'status' => MeetingRequestStatus::Requested->value,
                    'requested_at' => now(),
                ]
            );
        }

        ParentRequest::updateOrCreate(
            ['reference_number' => 'REQ-2026-DEMO1'],
            [
                'guardian_id' => $guardian->getKey(),
                'student_id' => $student->getKey(),
                'type' => ParentRequestType::TransferCertificate->value,
                'subject' => 'Transfer certificate request',
                'description' => 'Requesting a transfer certificate for a family move in June.',
                'status' => ParentRequestStatus::Submitted->value,
                'submitted_at' => now(),
            ]
        );
    }

    private function seedMessage(User $parent, Guardian $guardian, Student $student): void
    {
        $teacher = $student->homeroomTeacher();

        if (! $teacher) {
            return;
        }

        TeacherMessage::updateOrCreate(
            [
                'sender_id' => $parent->getKey(),
                'recipient_id' => $teacher->getKey(),
                'subject' => 'Excursion consent form',
            ],
            [
                'recipient_type' => 'teacher',
                'body' => 'Hello, could the consent form for the upcoming excursion be sent home? Thank you.',
                'message_type' => 'individual',
                'status' => 'sent',
            ]
        );

        TeacherMessage::updateOrCreate(
            [
                'sender_id' => $teacher->getKey(),
                'recipient_id' => $parent->getKey(),
                'subject' => 'Excursion consent form',
            ],
            [
                'recipient_type' => 'guardian',
                'body' => 'Of course, your child will bring a copy home tomorrow.',
                'message_type' => 'individual',
                'status' => 'sent',
            ]
        );
    }
}
