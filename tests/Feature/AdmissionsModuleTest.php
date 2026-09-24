<?php

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\GradeCapacity;
use App\Domains\Cms\Models\Inquiry;
use App\Support\Enums\AdmissionStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::SchoolAdmin->value => 'School Admin',
            RoleName::Principal->value => 'Principal',
            RoleName::Registrar->value => 'Registrar',
            RoleName::Reception->value => 'Reception',
            RoleName::Teacher->value => 'Teacher',
        ] as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $roleName)->firstOrFail());

        return $user;
    }

    private function currentYear(): AcademicYear
    {
        return AcademicYear::query()->firstOrCreate(
            ['name' => '2026/27'],
            AcademicYear::factory()->current()->raw(),
        );
    }

    private function grade(): GradeLevel
    {
        return GradeLevel::factory()->create([
            'name' => 'Grade 1',
            'code' => '1',
            'sort_order' => 2,
        ]);
    }

    public function test_registrar_can_open_the_admissions_dashboard(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->currentYear();

        $this->actingAs($registrar)
            ->get(route('admissions.dashboard'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_the_admissions_area(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);

        $this->actingAs($teacher)
            ->get(route('admissions.dashboard'))
            ->assertForbidden();
    }

    public function test_registrar_can_open_the_applications_list(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->get(route('admissions.applications.index'))
            ->assertOk();
    }

    public function test_inquiry_can_be_handled_into_an_application_draft(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->currentYear();
        $grade = $this->grade();

        $inquiry = Inquiry::factory()->create(['type' => 'admissions']);

        $this->actingAs($registrar)
            ->post(route('admissions.inquiries.handle', $inquiry), [
                'grade_level_id' => $grade->getKey(),
                'intake_academic_year_id' => $this->currentYear()->getKey(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNotNull($inquiry->fresh()->handled_at);

        $draft = AdmissionApplication::where('source_inquiry_id', $inquiry->getKey())->first();
        $this->assertNotNull($draft);
        $this->assertSame(AdmissionStatus::Draft->value, $draft->status);
    }

    public function test_application_lifecycle_from_submit_through_to_decision(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $year = $this->currentYear();
        $grade = $this->grade();

        $application = AdmissionApplication::factory()->create([
            'intake_academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
            'created_by' => $registrar->getKey(),
        ]);

        $this->actingAs($registrar)
            ->post(route('admissions.applications.submit', $application))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(
            AdmissionStatus::Submitted->value,
            AdmissionApplication::find($application->getKey())->status
        );

        $this->actingAs($registrar)
            ->post(route('admissions.applications.decide', $application), ['decision' => 'approved'])
            ->assertRedirect();

        $this->assertSame(
            AdmissionStatus::Approved->value,
            AdmissionApplication::find($application->getKey())->status
        );
    }

    public function test_promoting_and_enrolling_creates_a_student(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $year = $this->currentYear();
        $grade = $this->grade();

        $application = AdmissionApplication::factory()->create([
            'intake_academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
            'created_by' => $registrar->getKey(),
        ]);

        $this->actingAs($registrar)
            ->post(route('admissions.applications.submit', $application))
            ->assertRedirect();

        $this->actingAs($registrar)
            ->post(route('admissions.applications.decide', $application), ['decision' => 'approved'])
            ->assertRedirect();

        $this->actingAs($registrar)
            ->post(route('admissions.applications.enroll', $application))
            ->assertRedirect();

        $application->refresh();

        $this->assertSame(AdmissionStatus::Enrolled->value, $application->status);
        $this->assertNotNull($application->student_id);
    }
    public function test_rejected_decision_sets_rejected_status(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $application = AdmissionApplication::factory()->submitted()->create();

        $this->actingAs($registrar)
            ->post(route('admissions.applications.decide', $application), [
                'decision' => 'rejected',
                'comment' => 'Admission requirements were not met.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $application->refresh();

        $this->assertSame(AdmissionStatus::Rejected->value, $application->status);
        $this->assertSame('rejected', $application->decision);
        $this->assertNotNull($application->decided_at);
    }

    public function test_waitlisted_decision_sets_waitlisted_status_and_position(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $application = AdmissionApplication::factory()->submitted()->create();

        $this->actingAs($registrar)
            ->post(route('admissions.applications.decide', $application), [
                'decision' => 'waitlisted',
                'comment' => 'Please keep the applicant on the waiting list.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $application->refresh();

        $this->assertSame(AdmissionStatus::Waitlisted->value, $application->status);
        $this->assertSame('waitlisted', $application->decision);
        $this->assertSame(1, $application->waitlist_position);
        $this->assertNotNull($application->waitlisted_at);
    }

    public function test_full_grade_automatically_moves_approved_decision_to_waitlist(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $year = $this->currentYear();
        $grade = $this->grade();

        GradeCapacity::create([
            'academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
            'capacity' => 0,
            'allow_override' => false,
        ]);

        $application = AdmissionApplication::factory()->submitted()->create([
            'intake_academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
        ]);

        $this->actingAs($registrar)
            ->post(route('admissions.applications.decide', $application), [
                'decision' => 'approved',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(
            AdmissionStatus::Waitlisted->value,
            $application->fresh()->status
        );
    }

    public function test_teacher_cannot_decide_an_admission_application(): void
    {
        $teacher = $this->userWithRole(RoleName::Teacher->value);
        $application = AdmissionApplication::factory()->submitted()->create();

        $this->actingAs($teacher)
            ->post(route('admissions.applications.decide', $application), [
                'decision' => 'approved',
            ])
            ->assertForbidden();

        $this->assertSame(
            AdmissionStatus::Submitted->value,
            $application->fresh()->status
        );
    }

    public function test_admission_inquiry_conversion_uses_inquiry_data_and_is_one_time_only(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $year = $this->currentYear();
        $grade = $this->grade();

        $inquiry = Inquiry::factory()->create([
            'type' => 'admissions',
            'full_name' => 'Mekonnen Parent',
            'student_name' => 'Abebe Bekele',
            'email' => 'parent@example.test',
            'phone' => '+251911111111',
            'status' => 'new',
            'handled_at' => null,
            'handled_by' => null,
        ]);

        $this->actingAs($registrar)
            ->post(route('admissions.inquiries.handle', $inquiry), [
                'grade_level_id' => $grade->getKey(),
                'intake_academic_year_id' => $year->getKey(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $application = AdmissionApplication::where('source_inquiry_id', $inquiry->getKey())->firstOrFail();

        $this->assertSame(AdmissionStatus::Draft->value, $application->status);
        $this->assertSame('Abebe', $application->first_name);
        $this->assertSame('Bekele', $application->last_name);
        $this->assertNotNull($application->primaryParent);
        $this->assertSame('parent@example.test', $application->primaryParent->email);
        $this->assertNotNull($inquiry->fresh()->handled_at);

        $this->actingAs($registrar)
            ->post(route('admissions.inquiries.handle', $inquiry), [
                'grade_level_id' => $grade->getKey(),
                'intake_academic_year_id' => $year->getKey(),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('inquiry');

        $this->assertSame(1, AdmissionApplication::where('source_inquiry_id', $inquiry->getKey())->count());
    }

    public function test_non_admission_grade_cannot_be_used_for_an_application(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $year = $this->currentYear();
        $grade = GradeLevel::factory()->create([
            'name' => 'Grade 9',
            'code' => '9',
            'stage' => null,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $response = $this->actingAs($registrar)
            ->post(route('admissions.applications.store'), [
                'first_name' => 'Test',
                'last_name' => 'Applicant',
                'intake_academic_year_id' => $year->getKey(),
                'grade_level_id' => $grade->getKey(),
                'parents' => [[
                    'first_name' => 'Parent',
                    'last_name' => 'Applicant',
                    'relationship' => 'father',
                ]],
            ]);

        $response->assertRedirect()->assertSessionHasErrors('grade_level_id');
        $this->assertDatabaseMissing('admission_applications', ['first_name' => 'Test', 'last_name' => 'Applicant']);
    }

    public function test_parent_route_cannot_modify_a_guardian_belonging_to_another_application(): void
    {
        $registrar = $this->userWithRole(RoleName::Registrar->value);
        $year = $this->currentYear();
        $grade = $this->grade();
        $first = AdmissionApplication::factory()->create([
            'intake_academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
        ]);
        $second = AdmissionApplication::factory()->create([
            'intake_academic_year_id' => $year->getKey(),
            'grade_level_id' => $grade->getKey(),
        ]);
        $guardian = $second->guardians()->firstOrFail();

        $this->actingAs($registrar)
            ->put(route('admissions.parents.update', [$first, $guardian]), [
                'first_name' => 'Cross',
                'last_name' => 'Application',
                'relationship' => 'father',
            ])
            ->assertNotFound();

        $this->assertSame('guardian', $guardian->fresh()->relationship);
    }

}
