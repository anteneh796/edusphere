<?php

namespace Tests\Feature;

use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\NewsletterSubscriber;
use App\Domains\Cms\Models\Testimonial;
use App\Domains\Settings\Models\Setting;
use App\Support\Enums\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            RoleName::SuperAdmin->value => 'Super Admin',
            RoleName::Principal->value => 'Principal',
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

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Parent–Teacher Day',
            'description' => '<p>Meet your child\'s teacher.</p>',
            'location' => 'Main Assembly Hall',
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addMonth()->addHours(3)->format('Y-m-d\TH:i'),
            'featured' => '0',
            'published' => '1',
        ], $overrides);
    }

    private function testimonial_payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Amina Mohammed',
            'role' => 'Parent',
            'quote' => 'The teachers truly care about every child.',
            'sort_order' => '0',
            'published' => '1',
        ], $overrides);
    }

    public function test_super_admin_can_create_update_and_delete_an_event(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->post(route('cms.events.store'), $this->eventPayload())
            ->assertRedirect();

        $event = Event::where('title', 'Parent–Teacher Day')->first();
        $this->assertNotNull($event);

        $this->actingAs($admin)
            ->put(route('cms.events.update', $event), $this->eventPayload(['title' => 'Updated Event']))
            ->assertRedirect();

        $this->assertDatabaseHas('events', ['title' => 'Updated Event', 'published' => true]);

        $this->actingAs($admin)
            ->delete(route('cms.events.destroy', $event->fresh()))
            ->assertRedirect();

        $this->assertSoftDeleted('events', ['title' => 'Updated Event']);
    }

    public function test_wrong_role_cannot_access_event_management(): void
    {
        $student = $this->userWithRole(RoleName::Student->value);

        $this->actingAs($student)
            ->get(route('cms.events.create'))
            ->assertForbidden();
    }

    public function test_principal_can_create_and_update_a_testimonial(): void
    {
        $principal = $this->userWithRole(RoleName::Principal->value);

        $this->actingAs($principal)
            ->post(route('cms.testimonials.store'), $this->testimonial_payload())
            ->assertRedirect();

        $this->assertDatabaseHas('testimonials', ['name' => 'Amina Mohammed', 'published' => true]);

        $testimonial = Testimonial::where('name', 'Amina Mohammed')->first();

        $this->actingAs($principal)
            ->put(route('cms.testimonials.update', $testimonial), $this->testimonial_payload(['quote' => 'Updated quote.']))
            ->assertRedirect();

        $this->assertDatabaseHas('testimonials', ['quote' => 'Updated quote.']);
    }

    public function test_news_store_accepts_category(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->post(route('cms.news.store'), [
                'title' => 'Science Fair Wins',
                'body' => '<p>We placed first.</p>',
                'category' => 'achievements',
                'published' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('news', ['title' => 'Science Fair Wins', 'category' => 'achievements']);
    }

    public function test_gallery_items_can_be_media_type_video(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->post(route('cms.gallery.store'), [
                'caption' => 'Sports day reel',
                'album' => 'Sports',
                'media_type' => 'video',
                'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'sort_order' => '1',
                'published' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gallery_items', ['caption' => 'Sports day reel', 'media_type' => 'video']);
    }

    public function test_public_newsletter_subscription_works_and_validates(): void
    {
        $this->from(route('public.home'))
            ->post(route('public.newsletter.subscribe'), ['email' => 'parent@example.com'])
            ->assertRedirect(route('public.home'))
            ->assertSessionHas('newsletter');

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'parent@example.com']);

        $this->from(route('public.home'))
            ->post(route('public.newsletter.subscribe'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_newsletter_rejects_duplicate_active_email(): void
    {
        NewsletterSubscriber::subscribe('parent@example.com');

        $this->from(route('public.home'))
            ->post(route('public.newsletter.subscribe'), ['email' => 'parent@example.com'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_public_events_page_lists_published_and_featured_events(): void
    {
        $published = Event::factory()->featured()->create();
        Event::factory()->unpublished()->create(['title' => 'Hidden Event']);

        $this->get(route('public.events'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertSee('Featured')
            ->assertDontSee('Hidden Event');
    }

    public function test_public_gallery_groups_by_album_and_embeds_videos(): void
    {
        $image = GalleryItem::create([
            'caption' => 'Graduation photo',
            'album' => 'Graduation',
            'media_type' => 'image',
            'image_path' => 'cms/gallery/graduation.jpg',
            'sort_order' => 0,
            'published' => true,
        ]);

        $video = GalleryItem::create([
            'caption' => 'Sports day reel',
            'album' => 'Sports',
            'media_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'sort_order' => 0,
            'published' => true,
        ]);

        $this->get(route('public.gallery'))
            ->assertOk()
            ->assertSee('Graduation')
            ->assertSee('Sports')
            ->assertSee('youtube.com/embed/dQw4w9WgXcQ')
            ->assertSee('cms/gallery/graduation.jpg');
    }

    public function test_home_page_announcement_bar_renders_when_enabled(): void
    {
        Setting::set('announcement_enabled', true, 'school');
        Setting::set('announcement_text', 'School closed this Sunday', 'school');

        $this->get(route('public.home'))->assertSee('School closed this Sunday');

        Setting::set('announcement_enabled', false, 'school');

        $this->get(route('public.home'))->assertDontSee('School closed this Sunday');
    }

    public function test_settings_page_renders_for_super_admin(): void
    {
        $admin = $this->userWithRole(RoleName::SuperAdmin->value);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Announcement bar')
            ->assertSee('Social links');
    }

    public function test_news_show_displays_category_badge_and_author(): void
    {
        $author = User::factory()->create(['first_name' => 'Halima', 'last_name' => 'Hussein']);

        $item = NewsItem::factory()->create([
            'title' => 'Chess Champions',
            'category' => 'achievements',
            'author_id' => $author->getKey(),
            'published' => true,
        ]);

        $this->get(route('public.news-show', $item))
            ->assertOk()
            ->assertSee('Achievements')
            ->assertSee('Halima Hussein');
    }
}
