<?php

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
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
            ->assertSuccessful();

        $this->actingAs($registrar)
            ->post(route('admissions.applications.decide', $application), ['decision' => 'approved'])
            ->assertSuccessful();

        $this->actingAs($registrar)
            ->post(route('admissions.applications.enroll', $application))
            ->assertSuccessful();

        $application->refresh();

        $this->assertSame(AdmissionStatus::Enrolled->value, $application->status);
        $this->assertNotNull($application->student_id);
    }
}
