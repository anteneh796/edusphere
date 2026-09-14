<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Domains\Students\Models\Student;
use App\Http\Controllers\Controller;
use App\Support\Enums\RoleName;
use Illuminate\View\View;

class PublicWebsiteController extends Controller
{
    public function home(): View
    {
        $page = Page::where('slug', 'home')->where('published', true)->first();

        return view('public.pages.home', [
            'page' => $page,
            'news' => NewsItem::published()->latest('published_at')->limit(3)->get(),
            'gallery' => GalleryItem::published()->ordered()->limit(4)->get(),
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

    /* ------------------------------ Shared helpers ------------------------------ */

    public static function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_current', true)->first();
    }
}