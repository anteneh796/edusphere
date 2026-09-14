<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function index(): View
    {
        $this->authorize('view', Page::class);

        return view('cms.index', [
            'pages' => Page::withTrashed()->orderBy('slug')->get(),
            'publishedNews' => NewsItem::published()->count(),
            'totalNews' => NewsItem::withTrashed()->count(),
            'publishedGallery' => GalleryItem::published()->count(),
            'totalGallery' => GalleryItem::withTrashed()->count(),
        ]);
    }
}