<?php

namespace Tests\Feature;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Models\ExamResult;
use App\Domains\Exams\Models\ExamSubject;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\RoleName;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PortalWiringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::Principal->value => 'Principal',
            RoleName::Registrar->value => 'Registrar',
            RoleName::Teacher->value => 'Teacher',
            RoleName::FinanceOfficer->value => 'Finance Officer',
            RoleName::Student->value => 'Student',
            RoleName::Parent->value => 'Parent',
        ] as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $role)->first());

        return $user;
    }

    private function seededSchool(): array
    {
        AcademicYear::factory()->current()->create();

        $subject = Subject::create(['name' => 'Mathematics', 'code' => 'MATH']);

        $class = ClassRoom::create([
            'grade_level_id' => GradeLevel::create(['name' => 'Grade 5', 'code' => '5', 'sort_order' => 5])->getKey(),
            'academic_year_id' => AcademicYear::current()->first()->getKey(),
            'name' => '5 A',
            'capacity' => 40,
        ]);

        return compact('subject', 'class');
    }

    public function test_super_admin_and_principal_navigation_differ(): void
    {
        $superAdmin = $this->userWithRole(RoleName::SuperAdmin->value);
        $principal = $this->userWithRole(RoleName::Principal->value);

        $adminNav = collect(Navigation::forUser($superAdmin))->pluck('items')->flatten(1)->pluck('label')->all();
        $principalNav = collect(Navigation::forUser($principal))->pluck('items')->flatten(1)->pluck('label')->all();

        $this->assertContains('Users & Roles', $adminNav);
        $this->assertNotContains('Users & Roles', $principalNav);
        $this->assertContains('Settings', $adminNav);
        $this->assertNotContains('Settings', $principalNav);
        $this->assertContains('Website & News', $adminNav);
        $this->assertContains('Website & News', $principalNav);
    }

    public function test_principal_navigation_contains_cms(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $nav = collect(Navigation::forUser($principal))->pluck('items')->flatten(1)->pluck('route')->all();

        $this->assertContains('cms.index', $nav);
    }

    public function test_student_and_parent_navigation_serves_portal_routes(): void
    {
        $student = $this->userWithRole(RoleName::Student->value);
        $parent = $this->userWithRole(RoleName::Parent->value);

        $studentNav = collect(Navigation::forUser($student))->pluck('items')->flatten(1)->pluck('route')->all();
        $parentNav = collect(Navigation::forUser($parent))->pluck('items')->flatten(1)->pluck('route')->all();

        $this->assertContains('cms.student.dashboard', $studentNav);
        $this->assertContains('cms.student.attendance', $studentNav);
        $this->assertContains('cms.parent.dashboard', $parentNav);
        $this->assertContains('cms.parent.wards', $parentNav);
    }

    public function test_principal_cannot_access_users_management(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_principal_can_access_cms_module(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->get(route('cms.index'))
            ->assertOk();

        $this->actingAs($principal)
            ->get(route('cms.news.create'))
            ->assertOk();
    }

    public function test_super_admin_can_publish_a_news_item_with_image(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->post(route('cms.news.store'), [
                'title' => 'Grand Opening',
                'body' => '<p>Come visit our new campus.</p>',
                'published' => '1',
                'featured_image' => UploadedFile::fake()->image('banner.jpg'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('news', ['title' => 'Grand Opening', 'published' => true]);
        $this->assertStringStartsWith('cms/news/', NewsItem::where('title', 'Grand Opening')->first()->image_path);
    }

    public function test_super_admin_can_publish_a_gallery_item(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->post(route('cms.gallery.store'), [
                'caption' => 'Graduation day',
                'sort_order' => 1,
                'published' => '1',
                'image' => UploadedFile::fake()->image('photo.jpg'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gallery_items', ['caption' => 'Graduation day', 'published' => true]);
        $this->assertStringStartsWith('cms/gallery/', GalleryItem::where('caption', 'Graduation day')->first()->image_path);
    }

    public function test_student_portal_dashboard_renders_with_data(): void
    {
        ['subject' => $subject, 'class' => $class] = $this->seededSchool();

        $student = Student::factory()->create([
            'class_room_id' => $class->getKey(),
            'user_id' => null,
        ]);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', RoleName::Student->value)->first());
        $student->user_id = $user->getKey();
        $student->save();

        $exam = Exam::create([
            'academic_year_id' => AcademicYear::current()->first()->getKey(),
            'name' => 'Mid-term',
            'type' => 'midterm',
            'status' => ExamStatus::Published->value,
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        $examSubject = ExamSubject::factory()->create([
            'exam_id' => $exam->getKey(),
            'subject_id' => $subject->getKey(),
            'class_room_id' => $class->getKey(),
        ]);

        ExamResult::factory()->create([
            'exam_subject_id' => $examSubject->getKey(),
            'student_id' => $student->getKey(),
            'marks_obtained' => 88,
        ]);

        $this->actingAs($user)
            ->get(route('cms.student.dashboard'))
            ->assertOk()
            ->assertSee('Mathematics');
    }

    public function test_parent_portal_dashboard_renders_with_ward_and_result(): void
    {
        ['subject' => $subject, 'class' => $class] = $this->seededSchool();

        $guardian = Guardian::factory()->create();
        $parentUser = User::factory()->create();
        $parentUser->roles()->attach(Role::where('name', RoleName::Parent->value)->first());
        $guardian->user_id = $parentUser->getKey();
        $guardian->save();

        $student = Student::factory()->create(['class_room_id' => $class->getKey()]);
        $guardian->students()->attach($student);

        $exam = Exam::create([
            'academic_year_id' => AcademicYear::current()->first()->getKey(),
            'name' => 'Term Test',
            'type' => 'term',
            'status' => ExamStatus::Published->value,
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        $examSubject = ExamSubject::factory()->create([
            'exam_id' => $exam->getKey(),
            'subject_id' => $subject->getKey(),
            'class_room_id' => $class->getKey(),
        ]);

        ExamResult::factory()->create([
            'exam_subject_id' => $examSubject->getKey(),
            'student_id' => $student->getKey(),
            'marks_obtained' => 92,
        ]);

        $this->actingAs($parentUser)
            ->get(route('cms.parent.dashboard'))
            ->assertOk()
            ->assertSee('Mathematics');

        $this->actingAs($parentUser)
            ->get(route('cms.parent.wards.show', $student))
            ->assertOk()
            ->assertSee('Mathematics');
    }

    public function test_staff_dashboard_hides_admin_only_stats_from_registrar(): void
    {
        $this->seededSchool();

        $registrar = $this->userWithRole(RoleName::Registrar->value);

        $this->actingAs($registrar)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('System Users')
            ->assertDontSee('Defined Roles')
            ->assertDontSee('Audit entries will appear here');
    }

    public function test_super_admin_dashboard_shows_system_stats(): void
    {
        $this->seededSchool();

        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('System Users')
            ->assertSee('Defined Roles')
            ->assertSee('Audit entries will appear here');
    }

    public function test_news_item_can_be_created_and_listed(): void
    {
        NewsItem::factory()->create(['title' => 'Welcome Week', 'published' => true]);

        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->get(route('cms.news.index'))
            ->assertOk()
            ->assertSee('Welcome Week');
    }

    public function test_news_item_can_be_saved_as_unpublished_draft(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->post(route('cms.news.store'), [
                'title' => 'Draft Post',
                'body' => '<p>Not ready yet.</p>',
                'published' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('news', ['title' => 'Draft Post', 'published' => false]);
    }

    public function test_gallery_item_can_be_saved_as_unpublished(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->post(route('cms.gallery.store'), [
                'caption' => 'Hidden photo',
                'sort_order' => 0,
                'published' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gallery_items', ['caption' => 'Hidden photo', 'published' => false]);
    }

    public function test_website_page_can_be_edited(): void
    {
        $page = Page::create([
            'slug' => 'about',
            'title' => 'About Us',
            'subtitle' => 'Who we are',
            'body' => '<p>Original copy.</p>',
            'published' => true,
        ]);

        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->get(route('cms.pages.edit', $page))
            ->assertOk()
            ->assertSee('About Us');

        $this->actingAs($principal)
            ->put(route('cms.pages.update', $page), [
                'slug' => 'about',
                'title' => 'About Us',
                'subtitle' => 'Who we are',
                'body' => '<p>Updated copy.</p>',
                'published' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', ['slug' => 'about', 'body' => '<p>Updated copy.</p>']);
    }

    public function test_dashboard_redirects_role_portals(): void
    {
        $student = $this->userWithRole(RoleName::Student->value);
        $parent = $this->userWithRole(RoleName::Parent->value);

        $this->actingAs($student)->get(route('dashboard'))->assertRedirect(route('cms.student.dashboard'));
        $this->actingAs($parent)->get(route('dashboard'))->assertRedirect(route('cms.parent.dashboard'));
    }
}
