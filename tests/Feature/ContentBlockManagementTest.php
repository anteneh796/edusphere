<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\ContentBlock;
use App\Domains\Settings\Models\Setting;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBlockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::Teacher->value => 'Teacher',
        ] as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        Setting::set('school_name', 'EduSphere Academy');
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', RoleName::SuperAdmin->value)->first());

        return $user;
    }

    private function heroPayload(array $overrides = []): array
    {
        return array_merge([
            'page_slug' => 'home',
            'key' => 'hero',
            'eyebrow' => 'Welcome to',
            'title' => 'Where curiosity takes flight',
            'lead' => 'A brand new tagline for the homepage.',
            'body' => '',
            'payload' => json_encode(['items' => [
                ['icon' => 'check-circle', 'title' => 'Accredited curriculum'],
            ]]),
            'sort_order' => '1',
            'published' => '1',
        ], $overrides);
    }

    public function test_super_admin_can_create_a_block_and_it_appears_on_the_homepage(): void
    {
        $this->actingAs($this->admin())
            ->post(route('cms.content-blocks.store'), $this->heroPayload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Where curiosity takes flight');
    }

    public function test_update_block_is_reflected_on_the_public_page(): void
    {
        $block = ContentBlock::factory()->create([
            'page_slug' => 'about',
            'key' => 'hero',
            'title' => 'About :school',
            'lead' => 'Original lead copy.',
        ]);

        $this->actingAs($this->admin())
            ->put(route('cms.content-blocks.update', $block), $this->heroPayload([
                'page_slug' => 'about',
                'title' => 'Our story reimagined',
                'lead' => 'Updated lead copy.',
            ]))
            ->assertRedirect();

        $this->get(route('public.about'))
            ->assertOk()
            ->assertSee('Our story reimagined')
            ->assertSee('Updated lead copy')
            ->assertDontSee('Original lead copy');
    }

    public function test_unpublished_block_falls_back_to_the_default_heading(): void
    {
        ContentBlock::factory()->unpublished()->create([
            'page_slug' => 'home',
            'key' => 'hero',
            'title' => 'Hidden homepage headline',
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('Hidden homepage headline')
            ->assertSee('Welcome to EduSphere Academy');
    }

    public function test_teacher_cannot_access_content_block_management(): void
    {
        $teacher = User::factory()->create();
        $teacher->roles()->attach(Role::where('name', RoleName::Teacher->value)->first());

        $this->actingAs($teacher)
            ->get(route('cms.content-blocks.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_the_block_index_and_create_form(): void
    {
        ContentBlock::factory()->create([
            'page_slug' => 'home',
            'key' => 'programmes',
            'title' => 'A path for every age',
        ]);

        $this->actingAs($this->admin())
            ->get(route('cms.content-blocks.index'))
            ->assertOk()
            ->assertSee('Page Sections')
            ->assertSee('A path for every age');

        $this->actingAs($this->admin())
            ->get(route('cms.content-blocks.create'))
            ->assertOk()
            ->assertSee('page_slug')
            ->assertSee('sort_order');
    }

    public function test_super_admin_can_reorder_blocks_within_a_page(): void
    {
        $first = ContentBlock::factory()->create([
            'page_slug' => 'home',
            'key' => 'programmes',
            'sort_order' => 4,
        ]);
        $second = ContentBlock::factory()->create([
            'page_slug' => 'home',
            'key' => 'facilities',
            'sort_order' => 6,
        ]);

        $this->actingAs($this->admin())
            ->post(route('cms.content-blocks.reorder'), [
                'orders' => [
                    $first->id => '6',
                    $second->id => '4',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(6, $first->fresh()->sort_order);
        $this->assertSame(4, $second->fresh()->sort_order);
    }

    public function test_block_items_and_office_hours_power_the_contact_page(): void
    {
        ContentBlock::factory()->create([
            'page_slug' => 'contact',
            'key' => 'departments',
            'eyebrow' => 'Who to contact',
            'title' => 'Our departments',
            'payload' => ['items' => [
                ['icon' => 'user', 'title' => 'Admissions Office', 'text' => 'Enrolment questions.'],
            ]],
        ]);
        ContentBlock::factory()->create([
            'page_slug' => 'site',
            'key' => 'office-hours',
            'eyebrow' => 'Office hours',
            'body' => 'Mon–Fri · 9:00 am – 5:00 pm',
        ]);

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertSee('Admissions Office')
            ->assertSee('Enrolment questions.')
            ->assertSee('Mon–Fri · 9:00 am – 5:00 pm');
    }
}
