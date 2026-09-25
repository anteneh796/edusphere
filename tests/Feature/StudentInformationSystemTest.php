<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\EmergencyContact;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentDocument;
use App\Domains\Students\Models\StudentEnrollment;
use App\Domains\Students\Models\StudentTransfer;
use App\Support\Enums\RoleName;
use App\Support\Enums\StudentStatus;
use App\Support\Enums\StudentTimelineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentInformationSystemTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $currentYear;

    private AcademicYear $targetYear;

    private GradeLevel $grade5;

    private GradeLevel $grade6;

    private GradeLevel $grade8;

    private ClassRoom $class5A;

    private ClassRoom $class5B;

    private ClassRoom $class6A;

    private ClassRoom $class8A;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::SuperAdmin->value, 'label' => 'Super Admin']);
        Role::create(['name' => RoleName::Registrar->value, 'label' => 'Registrar']);
        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);

        $this->currentYear = AcademicYear::factory()->current()->create();
        $this->targetYear = AcademicYear::create([
            'name' => '2027/28',
            'start_date' => '2027-09-10',
            'end_date' => '2028-07-02',
            'is_current' => false,
        ]);

        $this->grade5 = GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 6]);
        $this->grade6 = GradeLevel::create(['name' => 'Grade 6', 'code' => '6', 'sort_order' => 7]);
        $this->grade8 = GradeLevel::create(['name' => 'Grade 8', 'code' => '8', 'sort_order' => 9]);

        $this->class5A = $this->makeClass($this->grade5, '5 A', $this->currentYear);
        $this->class5B = $this->makeClass($this->grade5, '5 B', $this->currentYear);
        $this->class6A = $this->makeClass($this->grade6, '6 A', $this->targetYear);
        $this->class8A = $this->makeClass($this->grade8, '8 A', $this->currentYear);
    }

    protected function makeClass(GradeLevel $grade, string $name, AcademicYear $year): ClassRoom
    {
        return ClassRoom::create([
            'grade_level_id' => $grade->getKey(),
            'academic_year_id' => $year->getKey(),
            'name' => $name,
            'capacity' => 40,
        ]);
    }

    protected function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->first());

        return $user;
    }

    protected function makeStudent(ClassRoom $class): Student
    {
        $student = Student::factory()->create([
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'academic_year_id' => $class->academic_year_id,
            'status' => StudentStatus::Active->value,
        ]);

        StudentEnrollment::factory()->create([
            'student_id' => $student->getKey(),
            'academic_year_id' => $class->academic_year_id,
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'roll_number' => StudentEnrollment::where('class_room_id', $class->getKey())
                ->where('academic_year_id', $class->academic_year_id)->count() + 1,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return $student;
    }

    protected function studentPayload(ClassRoom $class, string $nationalId = 'ET-48271234'): array
    {
        return [
            'first_name' => 'Tewodros',
            'last_name' => 'Gebre',
            'gender' => 'male',
            'date_of_birth' => '2014-03-12',
            'national_id' => $nationalId,
            'enrollment_date' => '2026-09-10',
            'grade_level_id' => $class->grade_level_id,
            'class_room_id' => $class->getKey(),
            'guardian' => [
                'first_name' => 'Gennet',
                'last_name' => 'Gebre',
                'relationship' => 'mother',
                'phone' => '+251911223344',
            ],
        ];
    }

    public function test_student_number_uses_bma_year_grade_section_and_roll(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)->post(route('students.store'), $this->studentPayload($this->class5A, 'NG-11111'))
            ->assertRedirect();
        $this->actingAs($registrar)->post(route('students.store'), $this->studentPayload($this->class5A, 'NG-22222'))
            ->assertRedirect();
        $this->actingAs($registrar)->post(route('students.store'), $this->studentPayload($this->class5B, 'NG-33333'))
            ->assertRedirect();

        $students = Student::orderBy('created_at')->get();

        $this->assertCount(3, $students);
        $this->assertSame('BMA265A001', $students[0]->student_number);
        $this->assertSame('BMA265A002', $students[1]->student_number);
        $this->assertSame('BMA265B001', $students[2]->student_number);

        $this->assertSame(1, (int) $students[0]->activeEnrollment()?->roll_number);
        $this->assertSame(2, (int) $students[1]->activeEnrollment()?->roll_number);
        $this->assertSame(1, (int) $students[2]->activeEnrollment()?->roll_number);
    }

    public function test_registrar_can_add_and_remove_emergency_contacts(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->post(route('students.emergency-contacts.store', $student), [
                'name' => 'Alem Berhanu',
                'relationship' => 'aunt',
                'phone' => '+251922334455',
                'priority' => 2,
                'authorized_pickup' => 1,
            ])
            ->assertRedirect();

        $contact = EmergencyContact::where('student_id', $student->getKey())->firstOrFail();
        $this->assertSame('Alem Berhanu', $contact->name);
        $this->assertSame(2, $contact->priority);
        $this->assertTrue((bool) $contact->authorized_pickup);

        $this->actingAs($registrar)
            ->delete(route('students.emergency-contacts.destroy', [$student, $contact]))
            ->assertRedirect();

        $this->assertDatabaseMissing('student_emergency_contacts', ['id' => $contact->getKey()]);
    }

    public function test_teacher_cannot_edit_medical_records(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($teacher)
            ->put(route('students.medical.update', $student), ['blood_group' => 'O+'])
            ->assertForbidden();
    }

    public function test_registrar_can_update_medical_record(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->put(route('students.medical.update', $student), [
                'blood_group' => 'O+',
                'allergies' => 'Peanuts',
                'emergency_hospital' => 'Yekatit 12 Hospital',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('student_medical_records', [
            'student_id' => $student->getKey(),
            'blood_group' => 'O+',
            'allergies' => 'Peanuts',
        ]);
        $this->assertDatabaseHas('student_timelines', [
            'student_id' => $student->getKey(),
            'type' => StudentTimelineType::MedicalUpdated->value,
        ]);
    }

    public function test_registrar_can_upload_and_verify_a_document(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->post(route('students.documents.store', $student), [
                'category' => 'birth_certificate',
                'name' => 'Birth certificate',
                'file' => UploadedFile::fake()->create('birth.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = StudentDocument::where('student_id', $student->getKey())->firstOrFail();
        $this->assertSame('birth_certificate', $document->category);
        $this->assertFalse((bool) $document->verified);

        $this->actingAs($registrar)
            ->put(route('students.documents.verify', [$student, $document]), ['verified' => 1])
            ->assertRedirect();

        $this->assertTrue((bool) $document->refresh()->verified);
        $this->assertNotNull($document->verified_at);
        $this->assertDatabaseHas('student_timelines', [
            'student_id' => $student->getKey(),
            'type' => StudentTimelineType::DocumentVerified->value,
        ]);
    }

    public function test_internal_transfer_moves_section_within_same_year_and_records_history(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);
        $originalNumber = $student->student_number;

        $this->actingAs($registrar)
            ->post(route('students.transfer.store', $student), [
                'type' => 'internal',
                'to_class_room_id' => $this->class5B->getKey(),
                'reason' => 'Balanced workloads',
            ])
            ->assertRedirect(route('students.show', $student));

        $student->refresh();

        $this->assertSame($this->class5B->getKey(), $student->class_room_id);
        $this->assertSame($this->grade5->getKey(), $student->grade_level_id);
        $this->assertSame($student->student_number, $originalNumber);
        $this->assertSame($this->class5B->getKey(), $student->activeEnrollment()?->class_room_id);
        $this->assertSame('active', $student->activeEnrollment()?->status);

        $this->assertDatabaseHas('student_transfers', [
            'student_id' => $student->getKey(),
            'type' => 'internal',
            'from_class_room_id' => $this->class5A->getKey(),
            'to_class_room_id' => $this->class5B->getKey(),
        ]);
        $this->assertDatabaseHas('student_timelines', [
            'student_id' => $student->getKey(),
            'type' => StudentTimelineType::SectionChange->value,
        ]);
    }

    public function test_external_transfer_marks_student_transferred_and_preserves_record(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->post(route('students.transfer.store', $student), [
                'type' => 'external',
                'destination_school' => 'Lideta Catholic Cathedral School',
                'certificate_number' => 'TC-2026-045',
                'reason' => 'Relocation to Addis Ababa',
                'transfer_date' => '2026-11-15',
            ])
            ->assertRedirect();

        $student->refresh();

        $this->assertSame(StudentStatus::Transferred->value, $student->status);
        $this->assertSame('left', $student->enrollments()->where('academic_year_id', $this->currentYear->getKey())->first()?->status);
        $this->assertNull($student->activeEnrollment());

        $this->assertDatabaseHas('student_transfers', [
            'student_id' => $student->getKey(),
            'type' => 'external',
            'destination_school' => 'Lideta Catholic Cathedral School',
            'certificate_number' => 'TC-2026-045',
        ]);
        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $student->getKey(),
            'from_status' => StudentStatus::Active->value,
            'to_status' => StudentStatus::Transferred->value,
        ]);
        $this->assertDatabaseHas('student_timelines', [
            'student_id' => $student->getKey(),
            'type' => StudentTimelineType::Transfer->value,
        ]);

        $this->assertDatabaseHas('students', ['id' => $student->getKey(), 'status' => StudentStatus::Transferred->value]);
    }

    public function test_promotion_creates_new_enrollment_and_locks_previous_year(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $studentA = $this->makeStudent($this->class5A);
        $studentB = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->post(route('students.promote.store'), [
                'class_room_id' => $this->class5A->getKey(),
                'target_academic_year_id' => $this->targetYear->getKey(),
                'note' => 'End of year promotion',
            ])
            ->assertRedirect();

        foreach ([$studentA, $studentB] as $student) {
            $student->refresh();

            $this->assertSame($this->grade6->getKey(), $student->grade_level_id);
            $this->assertSame($this->class6A->getKey(), $student->class_room_id);
            $this->assertSame($this->targetYear->getKey(), $student->academic_year_id);
            $this->assertSame(StudentStatus::Active->value, $student->status);
            $this->assertSame('promoted', $student->enrollments()->where('academic_year_id', $this->currentYear->getKey())->first()?->status);

            $this->assertDatabaseHas('student_enrollments', [
                'student_id' => $student->getKey(),
                'academic_year_id' => $this->targetYear->getKey(),
                'status' => 'active',
                'roll_number' => $student === $studentA ? 1 : 2,
            ]);
            $this->assertDatabaseHas('student_enrollments', [
                'student_id' => $student->getKey(),
                'academic_year_id' => $this->currentYear->getKey(),
                'status' => 'promoted',
                'result' => 'promoted',
            ]);
            $this->assertDatabaseHas('student_status_histories', [
                'student_id' => $student->getKey(),
                'to_status' => StudentStatus::Active->value,
                'reason' => 'End of year promotion',
            ]);
            $this->assertDatabaseHas('student_timelines', [
                'student_id' => $student->getKey(),
                'type' => StudentTimelineType::Promoted->value,
            ]);
        }
    }

    public function test_promoting_grade_8_graduates_students(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class8A);

        $this->actingAs($registrar)
            ->post(route('students.promote.store'), [
                'class_room_id' => $this->class8A->getKey(),
                'target_academic_year_id' => $this->targetYear->getKey(),
            ])
            ->assertRedirect();

        $student->refresh();

        $this->assertSame(StudentStatus::Graduated->value, $student->status);
        $this->assertSame('graduated', $student->enrollments()->where('academic_year_id', $this->currentYear->getKey())->first()?->status);
        $this->assertNull($student->activeEnrollment());
        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $student->getKey(),
            'to_status' => StudentStatus::Graduated->value,
        ]);
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->getKey(),
            'academic_year_id' => $this->currentYear->getKey(),
            'result' => 'graduated',
        ]);
    }

    public function test_teacher_can_view_class_roster(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $this->makeStudent($this->class5A);
        $this->makeStudent($this->class5A);

        $this->actingAs($teacher)
            ->get(route('students.roster', ['class_room_id' => $this->class5A->getKey()]))
            ->assertOk()
            ->assertSee('5 A');
    }

    public function test_external_transfer_row_stores_transfer_date(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->post(route('students.transfer.store', $student), [
                'type' => 'external',
                'destination_school' => 'St. Mary School',
                'transfer_date' => '2026-12-01',
            ])
            ->assertRedirect();

        $this->assertSame('2026-12-01', StudentTransfer::where('student_id', $student->getKey())->firstOrFail()->transfer_date?->format('Y-m-d'));
        $this->assertSame('2026-12-01', $student->refresh()->enrollments()->where('academic_year_id', $this->currentYear->getKey())->first()?->left_at?->format('Y-m-d'));
    }

    public function test_student_dashboard_shows_current_year_lifecycle_metrics(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $this->makeStudent($this->class5A);
        $this->makeStudent($this->class5B);
        $grade8 = $this->makeStudent($this->class8A);

        $this->actingAs($registrar)
            ->get(route('students.dashboard'))
            ->assertOk()
            ->assertSee('Student Information System')
            ->assertSee('2')
            ->assertSee('1');
    }

    public function test_registrar_can_export_filtered_student_register_as_csv(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $student = $this->makeStudent($this->class5A);

        $this->actingAs($registrar)
            ->get(route('students.export', ['class_room_id' => $this->class5A->getKey()]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', fn ($value) => str_contains($value, 'edusphere-students-'))
            ->assertStreamedContent($student->student_number);
    }

    public function test_registration_can_create_optional_emergency_contact(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->post(route('students.store'), [
                ...$this->studentPayload($this->class5A, 'NG-EMERGENCY-1'),
                'emergency_contact' => [
                    'name' => 'Alem Berhanu',
                    'relationship' => 'aunt',
                    'phone' => '+251922334455',
                    'priority' => 1,
                    'authorized_pickup' => 1,
                ],
            ])
            ->assertRedirect();

        $student = Student::where('national_id', 'NG-EMERGENCY-1')->firstOrFail();
        $this->assertDatabaseHas('student_emergency_contacts', [
            'student_id' => $student->getKey(),
            'name' => 'Alem Berhanu',
            'phone' => '+251922334455',
            'authorized_pickup' => 1,
        ]);
    }

}
