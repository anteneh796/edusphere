<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\Inquiry;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\NewsletterSubscriber;
use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Models\Testimonial;
use App\Domains\Cms\Requests\InquiryRequest;
use App\Domains\Cms\Requests\NewsletterSubscribeRequest;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Settings\Models\Setting;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\Enums\RoleName;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicWebsiteController extends Controller
{
    public function home(): View
    {
        $page = Page::where('slug', 'home')->where('published', true)->first();

        return view('public.pages.home', [
            'page' => $page,
            'news' => NewsItem::published()->latest('published_at')->limit(3)->get(),
            'gallery' => GalleryItem::published()->ordered()->limit(4)->get(),
            'events' => Event::published()->upcoming()->orderBy('starts_at')->limit(3)->get(),
            'testimonials' => Testimonial::published()->ordered()->limit(4)->get(),
            'principalName' => Setting::value('school_principal_name', 'The Principal'),
            'studentCount' => Student::active()->count(),
            'classRoomCount' => ClassRoom::whereHas('academicYear', fn ($query) => $query->where('is_current', true))->count(),
            'teacherCount' => User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count(),
            'subjectCount' => Subject::count(),
        ]);
    }

    public function show(string $page): View
    {
        $content = Page::where('slug', $page)->where('published', true)->firstOrFail();

        return view('public.pages.static', [
            'page' => $content,
            'tag' => ucwords(str_replace('-', ' ', $page)),
        ]);
    }

    public function about(): View
    {
        $page = Page::where('slug', 'about')->where('published', true)->first();

        return view('public.pages.about', [
            'page' => $page,
            'principalName' => Setting::value('school_principal_name', 'The Principal'),
            'stats' => [
                ['label' => __('Students'), 'value' => Student::active()->count()],
                ['label' => __('Teachers'), 'value' => User::whereHas('roles', fn ($query) => $query->where('name', RoleName::Teacher->value))->count()],
                ['label' => __('Years of experience'), 'value' => 15],
                ['label' => __('Clubs & activities'), 'value' => 24],
            ],
        ]);
    }

    public function academics(): View
    {
        return view('public.pages.academics');
    }

    public function admissions(): View
    {
        return view('public.pages.admissions');
    }

    public function contact(): View
    {
        return view('public.pages.contact');
    }

    public function apply(): View
    {
        return view('public.pages.apply');
    }

    public function news(): View
    {
        return view('public.pages.news', [
            'items' => NewsItem::published()->latest('published_at')->paginate(9),
        ]);
    }

    public function newsShow(NewsItem $news): View
    {
        abort_unless($news->isPublished(), 404);

        $related = NewsItem::published()
            ->whereKeyNot($news->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.pages.news-show', [
            'item' => $news,
            'related' => $related,
        ]);
    }

    public function gallery(): View
    {
        return view('public.pages.gallery', [
            'items' => GalleryItem::published()->ordered()->get(),
        ]);
    }

    public function faculty(): View
    {
        $faculty = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', [RoleName::Teacher->value, RoleName::Principal->value]))
            ->with('roles:id,name')
            ->orderBy('last_name')
            ->get();

        return view('public.pages.faculty', compact('faculty'));
    }

    public function events(): View
    {
        $events = Event::published()
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Event $event) => $event->starts_at?->format('F Y') ?? 'Undated');

        return view('public.pages.events', compact('events'));
    }

    public function inquiryView(): View
    {
        return view('public.pages.inquiry');
    }

    public function loginGateway(): View
    {
        return view('public.pages.login-gateway');
    }

    public function inquiryStore(InquiryRequest $request): RedirectResponse
    {
        $inquiry = Inquiry::create($request->validated());

        NotificationService::inquiryReceived(
            $inquiry->full_name,
            $inquiry->type,
            route('public.inquiry'),
        );

        return back()->with('success', __('Thank you! Your inquiry has been received. Our admissions team will contact you shortly.'));
    }

    public function newsletterSubscribe(NewsletterSubscribeRequest $request): RedirectResponse
    {
        NewsletterSubscriber::subscribe($request->input('email'));

        return back()->with('newsletter', __('Thank you! You are now subscribed to school updates.'));
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        $results = collect();

        if ($query !== '') {
            $results = collect([
                'pages' => Page::published()
                    ->where(fn ($q) => $q->where('title', 'like', "%{$query}%")->orWhere('body', 'like', "%{$query}%"))
                    ->limit(10)
                    ->get(),
                'news' => NewsItem::published()
                    ->where(fn ($q) => $q->where('title', 'like', "%{$query}%")->orWhere('excerpt', 'like', "%{$query}%")->orWhere('body', 'like', "%{$query}%"))
                    ->limit(10)
                    ->get(),
                'events' => Event::published()
                    ->where(fn ($q) => $q->where('title', 'like', "%{$query}%")->orWhere('description', 'like', "%{$query}%"))
                    ->limit(10)
                    ->get(),
            ]);
        }

        return view('public.pages.search', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function sitemap(): Response
    {
        $urls = collect();

        $urls->push((object) ['loc' => route('public.home'), 'lastmod' => now()]);
        $urls->push((object) ['loc' => route('public.about'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.academics'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.admissions'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.contact'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.apply'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.login-gateway'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.news'), 'lastmod' => now()]);
        $urls->push((object) ['loc' => route('public.gallery'), 'lastmod' => now()]);
        $urls->push((object) ['loc' => route('public.faculty'), 'lastmod' => null]);
        $urls->push((object) ['loc' => route('public.events'), 'lastmod' => now()]);

        NewsItem::published()->orderByDesc('published_at')->get()
            ->each(fn (NewsItem $item) => $urls->push((object) [
                'loc' => route('public.news-show', $item),
                'lastmod' => $item->published_at,
            ]));

        // Events are represented by the public calendar page itself; do not
        // add the same URL once per event to the sitemap.
        $latestEvent = Event::published()->orderByDesc('updated_at')->first();
        if ($latestEvent) {
            $urls->push((object) [
                'loc' => route('public.events'),
                'lastmod' => $latestEvent->updated_at,
            ]);
        }

        Page::published()->whereNotIn('slug', ['home', 'about', 'academics', 'admissions', 'contact'])->get()
            ->each(fn (Page $page) => $urls->push((object) [
                'loc' => route('public.page', $page->slug),
                'lastmod' => $page->updated_at,
            ]));

        return response(
            view('public.seo.sitemap', ['urls' => $urls])->render(),
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    public function robots(): Response
    {
        $contents = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /login',
            'Disallow: /dashboard',
            'Disallow: /student/',
            'Disallow: /parent/',
            '',
            'Sitemap: '.route('public.sitemap'),
        ]);

        return response($contents, 200, ['Content-Type' => 'text/plain']);
    }

    public function locale(Request $request, string $locale): JsonResponse|RedirectResponse
    {
        if (! in_array($locale, ['en', 'am'], true)) {
            $locale = 'en';
        }

        $request->session()->put('locale', $locale);

        app()->setLocale($locale);

        return $request->wantsJson()
            ? response()->json(['locale' => $locale])
            : redirect()->back();
    }

    public static function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_current', true)->first();
    }
}
