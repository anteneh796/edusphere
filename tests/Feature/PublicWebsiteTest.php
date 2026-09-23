<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Models\Inquiry;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleName::Teacher->value, 'label' => 'Teacher']);
        Role::create(['name' => RoleName::Principal->value, 'label' => 'Principal']);
    }

    protected function staffMember(string $role, string $firstName, string $lastName): User
    {
        $user = User::factory()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
        $user->roles()->attach(Role::where('name', $role)->first());

        return $user;
    }

    public function test_guest_can_view_faculty_of_teachers_and_principal(): void
    {
        $this->staffMember(RoleName::Teacher->value, 'Selam', 'Tadesse');
        $this->staffMember(RoleName::Principal->value, 'Dawit', 'Haile');

        $this->get(route('public.faculty'))
            ->assertOk()
            ->assertSee('Selam Tadesse')
            ->assertSee('Dawit Haile');
    }

    public function test_guest_cannot_see_non_staff_users_on_the_faculty_page(): void
    {
        $student = User::factory()->create(['first_name' => 'Hanna', 'last_name' => 'Alemu']);
        $student->roles()->attach(Role::create(['name' => RoleName::Student->value, 'label' => 'Student']));

        $this->get(route('public.faculty'))
            ->assertOk()
            ->assertDontSee('Hanna Alemu');
    }

    public function test_guest_can_view_published_events_grouped_by_month(): void
    {
        $event = Event::factory()->create([
            'title' => 'Annual Sports Day',
            'starts_at' => now()->addWeeks(2),
        ]);

        $this->get(route('public.events'))
            ->assertOk()
            ->assertSee('Annual Sports Day');
    }

    public function test_unpublished_events_are_hidden_from_guests(): void
    {
        $event = Event::factory()->unpublished()->create(['title' => 'Secret Staff Retreat']);

        $this->get(route('public.events'))
            ->assertOk()
            ->assertDontSee('Secret Staff Retreat');
    }

    public function test_guest_can_submit_an_inquiry(): void
    {
        $this->post(route('public.inquiry.store'), [
            'type' => 'admissions',
            'full_name' => 'Leah Bekele',
            'email' => 'leah@example.com',
            'phone' => '+251911223344',
            'student_name' => 'Nathan Bekele',
            'grade_level' => 'Grade 3',
            'message' => 'We would like a tour of the campus.',
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'type' => 'admissions',
            'full_name' => 'Leah Bekele',
            'email' => 'leah@example.com',
            'status' => 'new',
        ]);
    }

    public function test_missing_required_fields_are_rejected(): void
    {
        $this->post(route('public.inquiry.store'), [
            'full_name' => '',
            'email' => 'not-an-email',
        ])
            ->assertSessionHasErrors(['type', 'full_name', 'email']);

        $this->assertSame(0, Inquiry::count());
    }

    public function test_invalid_inquiry_type_is_rejected(): void
    {
        $this->post(route('public.inquiry.store'), [
            'type' => 'billing',
            'full_name' => 'Leah Bekele',
            'email' => 'leah@example.com',
        ])
            ->assertSessionHasErrors('type');

        $this->assertSame(0, Inquiry::count());
    }
}
