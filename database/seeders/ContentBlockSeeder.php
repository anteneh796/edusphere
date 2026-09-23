<?php

namespace Database\Seeders;

use App\Domains\Cms\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            /* ------------------------------- Home ------------------------------- */
            'home.hero' => [
                'title' => 'Where every child grows, achieves and belongs.',
                'lead' => 'A nurturing learning community committed to academic excellence, character, and service from early years through Grade 8.',
                'payload' => ['items' => [
                    ['icon' => 'check-circle', 'title' => 'Accredited curriculum'],
                    ['icon' => 'check-circle', 'title' => 'Caring, qualified faculty'],
                ]],
                'sort_order' => 1,
            ],
            'home.stats' => [
                'payload' => ['items' => [
                    ['title' => 'Happy students'],
                    ['title' => 'Dedicated teachers'],
                    ['title' => 'Classes'],
                    ['title' => 'Subjects on offer'],
                ]],
                'sort_order' => 2,
            ],
            'home.principal-message' => [
                'eyebrow' => 'A word from our Principal',
                'title' => 'A community built on care and curiosity',
                'body' => 'At :school, every child is known by name and loved as our own. We blend rigorous academics with the warmth of a real community, so your child leaves each day a little more confident, a little more curious, and a little more kind.',
                'sort_order' => 3,
            ],
            'home.programmes' => [
                'title' => 'A path for every age',
                'lead' => 'From joyful early years to confident Grade 8 graduates, our programmes grow with your child.',
                'payload' => ['items' => [
                    ['icon' => 'sprout', 'title' => 'Early Years', 'detail' => 'KG 1 – KG 2', 'text' => 'Playful, language-rich discovery that builds a lifelong love of learning.'],
                    ['icon' => 'book-open', 'title' => 'Primary', 'detail' => 'Grades 1 – 6', 'text' => 'Strong foundations in literacy, numeracy and character.'],
                    ['icon' => 'beaker', 'title' => 'Secondary', 'detail' => 'Grades 7 – 8', 'text' => 'Inquiry-based learning across sciences, arts and humanities, with growing independence.'],
                ]],
                'sort_order' => 4,
            ],
            'home.why-us' => [
                'eyebrow' => 'Why families choose us',
                'title' => 'The :school difference',
                'lead' => 'Six reasons our community is so special.',
                'payload' => ['items' => [
                    ['icon' => 'users', 'title' => 'Small class sizes', 'text' => 'Every child is seen and supported — no one is left behind.'],
                    ['icon' => 'award', 'title' => 'Qualified, caring faculty', 'text' => 'Experienced teachers who know your child by name and by story.'],
                    ['icon' => 'beaker', 'title' => 'Hands-on STEM labs', 'text' => 'Inquiry, robotics and real experiments from the primary years.'],
                    ['icon' => 'shield-heart', 'title' => 'Safe, joyful campus', 'text' => 'A secure, green campus designed around children\'s wellbeing.'],
                    ['icon' => 'globe', 'title' => 'English + Amharic', 'text' => 'Bilingual excellence with strong national identity.'],
                    ['icon' => 'music', 'title' => 'Arts, sport & clubs', 'text' => 'Choir, football, debate and 20+ after-school clubs.'],
                ]],
                'sort_order' => 5,
            ],
            'home.facilities' => [
                'eyebrow' => 'Campus life',
                'title' => 'Facilities designed for learning',
                'lead' => 'Modern spaces that spark curiosity and support wellbeing.',
                'payload' => ['items' => [
                    ['icon' => 'beaker', 'title' => 'STEM & Innovation Lab', 'text' => '3D printers, robotics and a makerspace for every grade.'],
                    ['icon' => 'dumbbell', 'title' => 'Sports & Playgrounds', 'text' => 'Pitch, courts and safe play areas for every age.'],
                    ['icon' => 'music', 'title' => 'Music & Performing Arts Hall', 'text' => 'Choir, instruments and theatrical productions.'],
                    ['icon' => 'bus', 'title' => 'Transport & Safety', 'text' => 'Guarded gates, CCTV and supervised school transport.'],
                    ['icon' => 'sprout', 'title' => 'Green Campus', 'text' => 'Gardens, trees and outdoor classrooms across campus.'],
                ]],
                'sort_order' => 6,
            ],
            'home.events' => [
                'eyebrow' => 'What\'s happening',
                'title' => 'Upcoming events',
                'sort_order' => 7,
            ],
            'home.news' => [
                'eyebrow' => 'From the campus',
                'title' => 'Latest news',
                'sort_order' => 8,
            ],
            'home.gallery' => [
                'eyebrow' => 'Student life',
                'title' => 'Campus in pictures',
                'sort_order' => 9,
            ],
            'home.testimonials' => [
                'eyebrow' => 'Kind words',
                'title' => 'What our families say',
                'sort_order' => 10,
            ],
            'home.cta' => [
                'eyebrow' => 'Enrolment open',
                'title' => 'Take the first step with :school',
                'lead' => 'Book a campus tour, ask a question, or submit your application today.',
                'sort_order' => 11,
            ],

            /* ------------------------------- About ------------------------------ */
            'about.hero' => [
                'eyebrow' => 'Our story',
                'title' => 'About :school',
                'lead' => 'A modern learning community committed to academic excellence, character, and service.',
                'sort_order' => 1,
            ],
            'about.mv-cards' => [
                'payload' => ['items' => [
                    ['icon' => 'target', 'title' => 'Our Mission', 'text' => 'To inspire every student to become a curious, confident, and compassionate citizen ready to lead in Ethiopia and beyond.'],
                    ['icon' => 'eye', 'title' => 'Our Vision', 'text' => 'A joyful, inclusive community where every child is known, challenged, and supported to discover their full potential.'],
                    ['icon' => 'heart', 'title' => 'Our Values', 'text' => 'Integrity, curiosity, respect, resilience, and service guide everything we do — in the classroom and beyond it.'],
                ]],
                'sort_order' => 2,
            ],
            'about.story' => [
                'eyebrow' => 'A 15-year journey',
                'title' => 'From one classroom to a vibrant campus',
                'body' => '<p>Founded with a belief that every child deserves an education that is both challenging and caring, our school has grown from a single classroom into a full preschool through Grade 8 campus.</p>',
                'payload' => ['items' => [
                    ['title' => 'Students'],
                    ['title' => 'Teachers'],
                    ['title' => 'Years of experience'],
                    ['title' => 'Clubs & activities'],
                ]],
                'sort_order' => 3,
            ],
            'about.leadership' => [
                'eyebrow' => 'Leadership',
                'title' => 'Meet our leadership team',
                'payload' => ['items' => [
                    ['title' => '', 'text' => 'Principal'],
                    ['title' => 'Hirut Lemma', 'text' => 'Registrar'],
                    ['title' => 'Selamawit Haile', 'text' => 'Director of Finance'],
                    ['title' => 'Mulugeta Assefa', 'text' => 'Head of Academics'],
                ]],
                'sort_order' => 4,
            ],
            'about.cta' => [
                'title' => 'Come see our campus for yourself',
                'lead' => 'We would love to welcome your family on a guided tour.',
                'sort_order' => 5,
            ],

            /* ----------------------------- Academics ----------------------------- */
            'academics.hero' => [
                'eyebrow' => 'Learning & academics',
                'title' => 'A path for every age',
                'lead' => 'From joyful early years through Grade 8 — our programmes grow with your child.',
                'sort_order' => 1,
            ],
            'academics.stages' => [
                'payload' => ['items' => [
                    ['icon' => 'sprout', 'title' => 'Early Years', 'grades' => 'KG 1 – KG 2', 'age' => 'Ages 4–6', 'text' => 'Playful, language-rich discovery that builds confidence, curiosity and a lifelong love of learning.', 'points' => ['Learning through play and inquiry', 'Phonics, numeracy and storytelling', 'Music, movement and art every week']],
                    ['icon' => 'book-open', 'title' => 'Primary School', 'grades' => 'Grades 1 – 6', 'age' => 'Ages 6–12', 'text' => 'Strong foundations in literacy, numeracy and character within a warm, structured classroom.', 'points' => ['English and Amharic literacy streams', 'Singapore-style mathematics', 'Science, ICT and PE each week']],
                    ['icon' => 'beaker', 'title' => 'Secondary School', 'grades' => 'Grades 7 – 8', 'age' => 'Ages 12–14', 'text' => 'Inquiry-led learning across sciences, arts and humanities, with growing independence and responsibility.', 'points' => ['Project-based science and robotics', 'Debate, drama and public speaking', 'Preparations for national examinations']],
                ]],
                'sort_order' => 2,
            ],
            'academics.extras' => [
                'eyebrow' => 'Beyond the classroom',
                'title' => 'Learning that extends everywhere',
                'payload' => ['items' => [
                    ['icon' => 'beaker', 'title' => 'STEM & Innovation', 'text' => 'Robotics, coding clubs and a makerspace where ideas become projects.'],
                    ['icon' => 'music', 'title' => 'Arts & Performance', 'text' => 'Choir, band, drama productions and the school-wide cultural festival.'],
                    ['icon' => 'dumbbell', 'title' => 'Sports Programme', 'text' => 'Football, basketball and athletics with coaching for all levels.'],
                    ['icon' => 'globe', 'title' => 'Languages', 'text' => 'English as the language of instruction with strong Amharic heritage.'],
                ]],
                'sort_order' => 3,
            ],
            'academics.cta' => [
                'title' => 'Ready to give your child a great start?',
                'lead' => 'Contact our admissions team to learn more, or start your application today.',
                'sort_order' => 4,
            ],

            /* ----------------------------- Admissions ---------------------------- */
            'admissions.hero' => [
                'eyebrow' => 'Join our community',
                'title' => 'Admissions',
                'lead' => 'Every child is welcome here. Our open enrolment policy means spaces are available across all grades throughout the year.',
                'sort_order' => 1,
            ],
            'admissions.steps' => [
                'payload' => ['items' => [
                    ['title' => 'Enquire', 'text' => 'Send us an inquiry or book a campus tour. Our admissions team will respond within one working day.'],
                    ['title' => 'Apply', 'text' => 'Submit the application form with a copy of your child\'s records. A small application fee applies.'],
                    ['title' => 'Meet us', 'text' => 'A friendly assessment and conversation help us understand your child\'s needs and strengths.'],
                    ['title' => 'Welcome aboard', 'text' => 'Once accepted, you\'ll receive your offer letter, welcome pack and all the details you need.'],
                ]],
                'sort_order' => 2,
            ],
            'admissions.documents' => [
                'eyebrow' => 'What to prepare',
                'title' => 'Documents & requirements',
                'payload' => ['items' => [
                    ['title' => 'Required documents', 'points' => ['Completed application form', 'Birth certificate or passport copy', 'Most recent report card / transcripts', 'Two recent passport photos', 'Medical / immunisation record']],
                    ['title' => 'Good to know', 'points' => ['Open enrolment all year, subject to space', 'Sibling discounts available', 'Scholarships for outstanding students', 'Termly fees with flexible payment plans']],
                ]],
                'sort_order' => 3,
            ],
            'admissions.age-table' => [
                'eyebrow' => 'Placement',
                'title' => 'Age criteria by grade',
                'lead' => 'Each level follows the Ethiopian academic year. Children should have turned the required age by September 30 of the admission year.',
                'payload' => ['items' => [
                    ['title' => 'KG 1', 'text' => '4–5 years'],
                    ['title' => 'KG 2', 'text' => '5–6 years'],
                    ['title' => 'Grade 1', 'text' => '6–7 years'],
                    ['title' => 'Grade 2', 'text' => '7–8 years'],
                    ['title' => 'Grade 3', 'text' => '8–9 years'],
                    ['title' => 'Grade 4', 'text' => '9–10 years'],
                    ['title' => 'Grade 5', 'text' => '10–11 years'],
                    ['title' => 'Grade 6', 'text' => '11–12 years'],
                    ['title' => 'Grade 7', 'text' => '12–13 years'],
                    ['title' => 'Grade 8', 'text' => '13–14 years'],
                ]],
                'sort_order' => 4,
            ],
            'admissions.faq' => [
                'eyebrow' => 'Good to know',
                'title' => 'Frequently asked questions',
                'payload' => ['items' => [
                    ['title' => 'Is there a deadline for applications?', 'text' => 'We follow open enrolment all year round, subject to space availability. Most families apply between December and August for the following academic year.'],
                    ['title' => 'Are scholarships or discounts available?', 'text' => 'Yes — we offer sibling discounts and merit scholarships for outstanding students. Contact our admissions office for details.'],
                    ['title' => 'What language of instruction do you use?', 'text' => 'English is the language of instruction, with a strong Amharic literacy and heritage programme.'],
                    ['title' => 'Do you provide school transport?', 'text' => 'Yes, supervised school transport is available on selected routes. Availability and fees are confirmed when you enrol.'],
                    ['title' => 'Can I visit the campus before applying?', 'text' => 'Absolutely. Book a campus tour through our inquiry form and our admissions team will arrange a visit.'],
                    ['title' => 'Which national examinations do students sit?', 'text' => 'Students prepare for the Grade 8 national examinations. Our secondary programme (Grades 7–8) is designed for success in them.'],
                ]],
                'sort_order' => 5,
            ],
            'admissions.cta' => [
                'title' => 'Start your application today',
                'lead' => 'We would love to welcome your family.',
                'sort_order' => 6,
            ],

            /* ------------------------------ Contact ------------------------------ */
            'contact.hero' => [
                'eyebrow' => 'We would love to hear from you',
                'title' => 'Contact us',
                'lead' => 'Questions, tours, or just a friendly hello — reach out any time.',
                'sort_order' => 1,
            ],
            'contact.departments' => [
                'eyebrow' => 'Who to contact',
                'title' => 'Our departments',
                'lead' => 'Don\'t know where to start? Our main office will point you in the right direction.',
                'payload' => ['items' => [
                    ['icon' => 'user', 'title' => 'Admissions Office', 'text' => 'Applications, visits and enrolment questions — we would love to meet your family.'],
                    ['icon' => 'clipboard-check', 'title' => 'Academic Office', 'text' => 'Curriculum, reports, attendance and student wellbeing.'],
                    ['icon' => 'wallet', 'title' => 'Finance & Billing', 'text' => 'Fees, invoices, payment plans and scholarships.'],
                    ['icon' => 'settings', 'title' => 'Parent Support & ICT', 'text' => 'Portal access, transport and general family support.'],
                ]],
                'sort_order' => 2,
            ],

            /* ------------------------------- News ------------------------------- */
            'news.hero' => [
                'eyebrow' => 'From the campus',
                'title' => 'News & Stories',
                'lead' => 'Official announcements, event recaps and the everyday stories that make our community special.',
                'sort_order' => 1,
            ],

            /* ------------------------------ Events ------------------------------ */
            'events.hero' => [
                'eyebrow' => 'School Calendar',
                'title' => 'Events',
                'lead' => 'Mark your calendar — our community comes together throughout the year.',
                'sort_order' => 1,
            ],

            /* ------------------------------ Gallery ------------------------------ */
            'gallery.hero' => [
                'eyebrow' => 'Campus life',
                'title' => 'Photo & Video Gallery',
                'lead' => 'A window into our days — celebrations, classrooms, sports, films and the quiet moments in between.',
                'sort_order' => 1,
            ],

            /* --------------------------- Site-wide ------------------------------- */
            'site.office-hours' => [
                'eyebrow' => 'Office hours',
                'body' => 'Mon–Fri · 8:00 am – 4:30 pm',
                'sort_order' => 1,
            ],
        ];

        foreach ($blocks as $key => $data) {
            [$pageSlug, $keyName] = explode('.', $key, 2);

            ContentBlock::updateOrCreate(
                ['page_slug' => $pageSlug, 'key' => $keyName],
                $data + ['published' => true],
            );
        }
    }
}
