<?php

namespace Database\Seeders;

use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Models\Testimonial;
use App\Domains\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPages();
        $this->seedNews();
        $this->seedGallery();
        $this->seedEvents();
        $this->seedTestimonials();
    }

    private function seedSettings(): void
    {
        Setting::set('school_name', 'EduSphere Academy', 'general');
        Setting::set('school_tagline', 'Knowledge is Light', 'general');
        Setting::set('school_email', 'info@edusphere.com', 'general');
        Setting::set('school_phone', '+251 11 000 0000', 'general');
        Setting::set('school_address', 'Bole Road, Addis Ababa, Ethiopia', 'general');
        Setting::set('school_principal_name', 'Bereket Mengistu', 'general');
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Welcome to EduSphere Academy',
                'subtitle' => 'A nurturing learning community where every child grows, achieves, and belongs.',
                'body' => '<p>Since our founding, EduSphere Academy has championed academic excellence, character, and service. Our teachers, families, and students work together every day to make learning joyful, rigorous, and meaningful.</p>',
            ],
            [
                'slug' => 'about',
                'title' => 'About EduSphere Academy',
                'subtitle' => 'A modern learning community committed to academic excellence, character, and service.',
                'body' => '<p>Founded with a belief that every child deserves an education that is both challenging and caring, EduSphere Academy has grown from a single classroom to a full preschool through grade 12 campus.</p><p>Our mission is to inspire students to become curious, confident, and compassionate citizens ready to lead in Ethiopia and beyond.</p>',
            ],
            [
                'slug' => 'academics',
                'title' => 'Academics',
                'subtitle' => 'A rigorous, student-centred curriculum from early years through college preparation.',
                'body' => '<p>Our academic program balances depth and breadth. From the joyful discovery of the early years to the analytic rigour of our college-preparatory track, every child is known, challenged, and supported.</p>',
            ],
            [
                'slug' => 'admissions',
                'title' => 'Admissions',
                'subtitle' => 'Applications are open for the coming academic year — we would love to welcome your family.',
                'body' => '<p>Admission to EduSphere Academy is based on a family interview and a developmental assessment appropriate to the child\'s age. We accept applications throughout the year and invite families to tour our campus.</p>',
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Us',
                'subtitle' => 'Reach our office, plan a visit, or ask about admissions.',
                'body' => '<p>Our admissions and general enquiries office is open Monday through Friday, 8:00 am – 4:30 pm.</p>',
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'subtitle' => 'How EduSphere Academy collects, uses, and protects your information.',
                'body' => '<p>Your privacy matters to us. This policy explains what information we collect, why we collect it, and how we keep it safe.</p><h3>What we collect</h3><p>We collect the details you share with us through applications, enquiries, and school systems — such as your name, contact details, and your child\'s educational records.</p><h3>How we use it</h3><p>We use this information to manage admissions, communicate with you, deliver education, and meet legal obligations. We never sell personal information to third parties.</p><h3>Your rights</h3><p>You may request a copy of the information we hold about you or ask us to correct it. Contact the front office for any privacy-related questions.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function seedNews(): void
    {
        $items = [
            [
                'title' => 'EduSphere Academy Wins Regional Science Fair',
                'excerpt' => 'Our middle school team took first place at this year\'s regional science and innovation fair.',
                'body' => '<p>Students from grades 6–8 presented a solar-powered irrigation prototype and impressed the judges with their research and presentation.</p><p>The team will represent our region at the national fair in Addis Ababa this spring.</p>',
            ],
            [
                'title' => 'New STEM and Innovation Lab Grand Opening',
                'excerpt' => 'A brand-new makerspace and computer lab opened on campus this term.',
                'body' => '<p>The STEM lab features 3D printers, robotics kits, and a dedicated electronics workbench, giving students hands-on access to modern technology.</p><p>Students in every grade will use the space through our inquiry and club programmes.</p>',
            ],
            [
                'title' => 'Annual Sports Day a Celebration of Teamwork',
                'excerpt' => 'Athletics, fun relays, and house colours brought the whole school community together.',
                'body' => '<p>Our annual sports day was a joyful day of competition and community. From the youngest runners to the staff relay, everyone gave their best.</p>',
            ],
        ];

        foreach ($items as $index => $item) {
            $slug = str($item['title'])->slug('-', '_')->append('-'.($index + 1));

            NewsItem::updateOrCreate(
                ['slug' => $slug->value()],
                [
                    ...$item,
                    'published' => true,
                    'published_at' => now()->subDays($index * 7),
                ]
            );
        }
    }

    private function seedGallery(): void
    {
        $captions = [
            'Our seniors on graduation day',
            'Art class in the atelier',
            'Science experiments in the lab',
            'Football practice on the pitch',
            'The school choir in concert',
        ];

        foreach ($captions as $index => $caption) {
            GalleryItem::updateOrCreate(
                ['caption' => $caption],
                ['sort_order' => $index, 'published' => true]
            );
        }
    }

    private function seedEvents(): void
    {
        $events = [
            [
                'title' => 'Open House & Campus Tour',
                'location' => 'Main Campus, Bole Road',
                'starts_at' => now()->addDays(9)->setTime(9, 0),
                'ends_at' => now()->addDays(9)->setTime(12, 0),
                'description' => 'Families are invited to tour the campus and meet our teachers.',
            ],
            [
                'title' => 'Parent–Teacher Conferences',
                'location' => 'Middle School Building',
                'starts_at' => now()->addDays(21)->setTime(14, 0),
                'ends_at' => now()->addDays(21)->setTime(17, 0),
                'description' => 'An afternoon to discuss your child\'s progress with their teachers.',
            ],
            [
                'title' => 'STEM Exhibition Day',
                'location' => 'Innovation Lab',
                'starts_at' => now()->addDays(35)->setTime(10, 0),
                'ends_at' => now()->addDays(35)->setTime(14, 0),
                'description' => 'Students showcase their projects from across the science and technology curriculum.',
            ],
            [
                'title' => 'Cultural Day & Food Fair',
                'location' => 'School Grounds',
                'starts_at' => now()->addDays(48)->setTime(9, 0),
                'ends_at' => now()->addDays(48)->setTime(16, 0),
                'description' => 'A celebration of the many cultures represented in our community.',
            ],
        ];

        foreach ($events as $event) {
            $slugKey = str($event['title'])->slug('-')->value().'-'.now()->format('Y');

            Event::updateOrCreate(
                ['slug' => $slugKey],
                [
                    ...$event,
                    'featured' => $event['title'] === 'Open House & Campus Tour',
                    'published' => true,
                ]
            );
        }
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'name' => 'Sara Tesfaye',
                'role' => 'Parent of a Grade 4 student',
                'quote' => 'The teachers genuinely know my daughter and her own way of learning. She has never been happier going to school in the morning.',
                'sort_order' => 0,
            ],
            [
                'name' => 'Dawit Haile',
                'role' => 'Parent of a Grade 9 student',
                'quote' => 'EduSphere balances strong academics with character. Our son came home more confident, curious, and kind.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Meron Tadesse',
                'role' => 'Alumna, Class of 2024',
                'quote' => 'The opportunities here — from the STEM lab to the choir — shaped who I am today. I am proud to be an EduSphere alumna.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kalkidan Worku',
                'role' => 'Parent of twins in KG 2',
                'quote' => 'From the moment we toured, it felt like family. Our twins ran into their classroom on day one without looking back.',
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['name' => $testimonial['name']],
                [...$testimonial, 'published' => true]
            );
        }
    }
}
