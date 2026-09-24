<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Notifications\Models\Notification;
use App\Domains\ParentPortal\Models\AbsenceRequest;
use App\Domains\ParentPortal\Models\MeetingRequest;
use App\Domains\ParentPortal\Models\ParentRequest;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\TeacherPortal\Models\TeacherMessage;
use App\Support\Enums\AbsenceRequestStatus;
use App\Support\Enums\MeetingRequestStatus;
use App\Support\Enums\ParentRequestStatus;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function establishClass(): ClassRoom
    {
        $class = ClassRoom::where('name', '5 A')->first();

        $this->assertNotNull($class);

        return $class;
    }

    private function teacherFor(array $class): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('name', RoleName::Teacher->value)->firstOrFail());

        $subject = Subject::where('code', 'MATH')->firstOrFail();

        ClassSubject::updateOrCreate(
            ['class_room_id' => $class['class']->getKey(), 'subject_id' => $subject->getKey()],
            [
                'teacher_id' => $user->getKey(),
                'periods_per_week' => 5,
                'position' => 0,
                'is_homeroom' => true,
            ]
        );

        return $user;
    }

    private function guardianScenario(bool $finance = true): array
    {
        $class = $this->establishClass();

        $guardian = Guardian::factory()->create();
        $parent = User::factory()->create(['status' => 'active']);
        $parent->roles()->attach(Role::where('name', RoleName::Parent->value)->firstOrFail());
        $guardian->update(['user_id' => $parent->getKey()]);

        $student = Student::factory()->create([
            'class_room_id' => $class->getKey(),
            'academic_year_id' => AcademicYear::current()->first()->getKey(),
        ]);

        $guardian->students()->attach($student, [
            'is_primary' => true,
            'permissions' => array_merge(Guardian::defaultPermissions(), ['finance' => $finance]),
        ]);

        $teacher = $this->teacherFor(['class' => $class]);

        return compact('guardian', 'parent', 'student', 'teacher');
    }

    public function test_parent_can_view_academics_report_card_and_homework(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.academics'))
            ->assertOk();

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.report-card'))
            ->assertOk();

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.homework'))
            ->assertOk();
    }

    public function test_parent_cannot_view_an_unlinked_student_profile(): void
    {
        $scenario = $this->guardianScenario();

        $stranger = Student::factory()->create([
            'class_room_id' => $this->establishClass()->getKey(),
            'academic_year_id' => AcademicYear::current()->first()->getKey(),
        ]);

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.wards.show', $stranger))
            ->assertNotFound();
    }

    public function test_parent_cannot_switch_to_an_unlinked_ward(): void
    {
        $scenario = $this->guardianScenario();

        $stranger = Student::factory()->create([
            'class_room_id' => $this->establishClass()->getKey(),
            'academic_year_id' => AcademicYear::current()->first()->getKey(),
        ]);

        $this->actingAs($scenario['parent'])
            ->from(route('cms.parent.dashboard'))
            ->post(route('cms.parent.wards.switch'), ['student' => $stranger->getKey()])
            ->assertForbidden();
    }

    public function test_parent_can_explain_an_absence_for_a_ward(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->post(route('cms.parent.absence-requests.store'), [
                'student' => $scenario['student']->getKey(),
                'absence_date' => now()->addDay()->format('Y-m-d'),
                'reason' => 'Doctor appointment',
            ])
            ->assertRedirect(route('cms.parent.absence-requests'));

        $this->assertDatabaseHas('absence_requests', [
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'reason' => 'Doctor appointment',
            'status' => AbsenceRequestStatus::Submitted->value,
        ]);
    }

    public function test_parent_can_submit_a_service_request(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->post(route('cms.parent.requests.store'), [
                'student' => $scenario['student']->getKey(),
                'type' => 'transfer_certificate',
                'subject' => 'Transfer certificate please',
                'description' => 'Moving cities in June.',
            ])
            ->assertRedirect(route('cms.parent.requests'));

        $this->assertDatabaseHas('parent_requests', [
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'subject' => 'Transfer certificate please',
            'status' => ParentRequestStatus::Submitted->value,
        ]);
    }

    public function test_parent_can_request_a_meeting_with_the_homeroom_teacher(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->post(route('cms.parent.meetings.store'), [
                'student' => $scenario['student']->getKey(),
                'teacher_id' => $scenario['teacher']->getKey(),
                'meeting_type' => 'in_person',
                'preferred_date' => now()->addWeek()->format('Y-m-d'),
                'preferred_time' => '15:30',
                'reason' => 'Discuss progress',
            ])
            ->assertRedirect(route('cms.parent.meetings'));

        $this->assertDatabaseHas('meeting_requests', [
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'teacher_id' => $scenario['teacher']->getKey(),
            'status' => MeetingRequestStatus::Requested->value,
        ]);
    }

    public function test_parent_can_message_a_ward_teacher(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->post(route('cms.parent.messages.store'), [
                'recipient_uid' => $scenario['teacher']->getKey(),
                'subject' => 'Excursion consent',
                'body' => 'Please send the consent form home.',
            ])
            ->assertRedirect(route('cms.parent.messages'));

        $this->assertDatabaseHas('teacher_messages', [
            'sender_id' => $scenario['parent']->getKey(),
            'recipient_id' => $scenario['teacher']->getKey(),
            'recipient_type' => 'teacher',
            'subject' => 'Excursion consent',
        ]);
    }

    public function test_meeting_can_be_cancelled_while_requested(): void
    {
        $scenario = $this->guardianScenario();

        $meeting = MeetingRequest::create([
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'teacher_id' => $scenario['teacher']->getKey(),
            'meeting_type' => 'in_person',
            'preferred_date' => now()->addWeek()->format('Y-m-d'),
            'status' => MeetingRequestStatus::Requested->value,
            'requested_at' => now(),
        ]);

        $this->actingAs($scenario['parent'])
            ->post(route('cms.parent.meetings.cancel', $meeting))
            ->assertRedirect(route('cms.parent.meetings'));

        $this->assertDatabaseHas('meeting_requests', [
            'id' => $meeting->getKey(),
            'status' => MeetingRequestStatus::Cancelled->value,
        ]);
    }



    public function test_parent_can_mark_all_notifications_as_read(): void
    {
        $scenario = $this->guardianScenario();

        Notification::create([
            'user_id' => $scenario['parent']->getKey(),
            'type' => 'test',
            'title' => 'Hello',
            'category' => 'system',
            'priority' => 'low',
            'icon' => 'bell',
        ]);

        $this->actingAs($scenario['parent'])
            ->post(route('cms.parent.notifications.read-all'))
            ->assertRedirect(route('cms.parent.notifications'));

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $scenario['parent']->getKey(),
            'read_at' => null,
        ]);
    }

    public function test_staff_can_approve_an_absence_and_the_guardian_is_notified(): void
    {
        $scenario = $this->guardianScenario();

        $absence = AbsenceRequest::create([
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'absence_date' => now()->addDay()->format('Y-m-d'),
            'reason' => 'Sick',
            'status' => AbsenceRequestStatus::Submitted->value,
            'submitted_at' => now(),
        ]);

        $registrar = User::factory()->create(['status' => 'active']);
        $registrar->roles()->attach(Role::where('name', RoleName::Registrar->value)->firstOrFail());

        $this->actingAs($registrar)
            ->post(route('parent-services.absences.review', $absence), [
                'status' => AbsenceRequestStatus::Approved->value,
                'reviewer_note' => 'Noted',
            ])
            ->assertRedirect(route('parent-services.absences.index'));

        $this->assertDatabaseHas('absence_requests', [
            'id' => $absence->getKey(),
            'status' => AbsenceRequestStatus::Approved->value,
            'reviewer_note' => 'Noted',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $scenario['parent']->getKey(),
            'type' => 'absence',
        ]);
    }


    public function test_parent_can_open_all_portal_pages(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent']);

        foreach (['dashboard', 'wards', 'attendance', 'absence-requests', 'homework', 'academics', 'report-card', 'calendar', 'announcements', 'messages', 'meetings', 'requests', 'documents', 'notifications', 'settings'] as $page) {
            $this->get(route('cms.parent.'.$page))->assertOk();
        }

        $this->get(route('cms.parent.wards.show', $scenario['student']))->assertOk();
    }

    public function test_staff_can_open_guardian_service_pages(): void
    {
        $registrar = User::factory()->create(['status' => 'active']);
        $registrar->roles()->attach(Role::where('name', RoleName::Registrar->value)->firstOrFail());

        $this->actingAs($registrar)
            ->get(route('parent-services.absences.index'))
            ->assertOk();

        $this->actingAs($registrar)
            ->get(route('parent-services.requests.index'))
            ->assertOk();
    }


    public function test_teacher_can_review_meeting_requests_page(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['teacher'])
            ->get(route('cms.teacher.meetings'))
            ->assertOk();
    }

    public function test_parent_can_view_an_absence_that_is_still_requested(): void
    {
        $scenario = $this->guardianScenario();

        AbsenceRequest::create([
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'absence_date' => now()->addDay()->format('Y-m-d'),
            'reason' => 'Clinic',
            'status' => AbsenceRequestStatus::Submitted->value,
            'submitted_at' => now(),
        ]);

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.absence-requests'))
            ->assertOk();
    }

    public function test_parent_can_view_their_documents_page(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.documents'))
            ->assertOk();
    }

    public function test_parent_can_view_their_settings_page(): void
    {
        $scenario = $this->guardianScenario();

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.settings'))
            ->assertOk();
    }

    public function test_parent_can_view_their_notifications_list(): void
    {
        $scenario = $this->guardianScenario();

        Notification::create([
            'user_id' => $scenario['parent']->getKey(),
            'type' => 'welcome',
            'title' => 'Welcome',
            'category' => 'system',
            'priority' => 'low',
            'icon' => 'bell',
        ]);

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.notifications'))
            ->assertOk()
            ->assertSee('Welcome');
    }

    public function test_guardian_service_staff_can_review_a_request(): void
    {
        $scenario = $this->guardianScenario();

        $request = ParentRequest::create([
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'type' => 'transfer_certificate',
            'reference_number' => 'REQ-REVIEW-1',
            'subject' => 'Certificate please',
            'description' => 'Moving away.',
            'status' => ParentRequestStatus::Submitted->value,
            'submitted_at' => now(),
        ]);

        $registrar = User::factory()->create(['status' => 'active']);
        $registrar->roles()->attach(Role::where('name', RoleName::Registrar->value)->firstOrFail());

        $this->actingAs($registrar)
            ->post(route('parent-services.requests.process', $request), [
                'status' => ParentRequestStatus::Completed->value,
                'resolution' => 'Issued certificate.',
                'staff_note' => 'Done',
            ])
            ->assertRedirect(route('parent-services.requests.index'));

        $this->assertDatabaseHas('parent_requests', [
            'id' => $request->getKey(),
            'status' => ParentRequestStatus::Completed->value,
            'resolution' => 'Issued certificate.',
        ]);
    }

    public function test_teacher_can_review_a_meeting_request(): void
    {
        $scenario = $this->guardianScenario();

        $meeting = MeetingRequest::create([
            'guardian_id' => $scenario['guardian']->getKey(),
            'student_id' => $scenario['student']->getKey(),
            'teacher_id' => $scenario['teacher']->getKey(),
            'meeting_type' => 'in_person',
            'preferred_date' => now()->addWeek()->format('Y-m-d'),
            'status' => MeetingRequestStatus::Requested->value,
            'requested_at' => now(),
        ]);

        $this->actingAs($scenario['teacher'])
            ->post(route('cms.teacher.meetings.review', $meeting), [
                'status' => MeetingRequestStatus::Confirmed->value,
                'staff_note' => 'See you then.',
            ])
            ->assertRedirect(route('cms.teacher.meetings'));

        $this->assertDatabaseHas('meeting_requests', [
            'id' => $meeting->getKey(),
            'status' => MeetingRequestStatus::Confirmed->value,
            'staff_note' => 'See you then.',
        ]);
    }

    public function test_teacher_can_reply_to_a_parent_message(): void
    {
        $scenario = $this->guardianScenario();

        $message = TeacherMessage::create([
            'sender_id' => $scenario['parent']->getKey(),
            'recipient_type' => 'teacher',
            'recipient_id' => $scenario['teacher']->getKey(),
            'subject' => 'Help',
            'body' => 'Need help with something.',
            'message_type' => 'individual',
            'status' => 'sent',
        ]);

        $this->actingAs($scenario['teacher'])
            ->post(route('cms.teacher.messages.reply', $message), [
                'body' => 'Happy to help.',
            ])
            ->assertRedirect(route('cms.teacher.messages'));

        $this->assertDatabaseHas('teacher_messages', [
            'sender_id' => $scenario['teacher']->getKey(),
            'recipient_id' => $scenario['parent']->getKey(),
            'recipient_type' => 'guardian',
            'reply_to_id' => $message->getKey(),
            'body' => 'Happy to help.',
        ]);
    }

    public function test_only_parent_role_can_open_parent_portal(): void
    {
        $teacher = User::factory()->create(['status' => 'active']);
        $teacher->roles()->attach(Role::where('name', RoleName::Teacher->value)->firstOrFail());

        $this->actingAs($teacher)
            ->get(route('cms.parent.dashboard'))
            ->assertForbidden();
    }

    public function test_parent_cannot_view_another_parents_ward(): void
    {
        $scenario = $this->guardianScenario();

        $otherParent = User::factory()->create(['status' => 'active']);
        $otherParent->roles()->attach(Role::where('name', RoleName::Parent->value)->firstOrFail());
        $otherProfile = \\App\Domains\Students\Models\Guardian::factory()->create(['user_id' => $otherParent->getKey()]);
        $otherStudent = Student::factory()->create([
            'class_room_id' => $scenario['student']->class_room_id,
            'academic_year_id' => $scenario['student']->academic_year_id,
        ]);
        $otherProfile->students()->attach($otherStudent, ['is_primary' => true]);

        $this->actingAs($scenario['parent'])
            ->get(route('cms.parent.wards.show', $otherStudent))
            ->assertForbidden();
    }

    public function test_standalone_guardian_management_is_removed(): void
    {
        $principal = User::factory()->create(['status' => 'active']);
        $principal->roles()->attach(Role::where('name', RoleName::Principal->value)->firstOrFail());

        $this->actingAs($principal)
            ->get('/guardians')
            ->assertNotFound();

        $this->assertDatabaseMissing('permissions', ['name' => 'guardians.view']);
        $this->assertDatabaseMissing('permissions', ['name' => 'guardians.create']);
        $this->assertDatabaseMissing('permissions', ['name' => 'guardians.edit']);
        $this->assertDatabaseMissing('permissions', ['name' => 'guardians.delete']);
    }

}
