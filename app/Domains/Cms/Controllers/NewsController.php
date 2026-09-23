<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Requests\StoreNewsRequest;
use App\Domains\Cms\Requests\UpdateNewsRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', NewsItem::class);

        $items = NewsItem::withTrashed()
            ->with('author')
            ->when($request->query('filter') === 'drafts', fn ($query) => $query->where('published', false))
            ->when($request->query('filter') === 'published', fn ($query) => $query->where('published', true))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('cms.news.index', [
            'items' => $items,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', NewsItem::class);

        return view('cms.news.form', ['item' => null]);
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $this->authorize('create', NewsItem::class);

        $item = NewsItem::create([
            'title' => $request->input('title'),
            'slug' => $this->resolveSlug($request),
            'category' => $request->input('category'),
            'excerpt' => $request->input('excerpt'),
            'body' => $request->input('body'),
            'image_path' => $this->storeFeaturedImage($request) ?? $request->input('image_path'),
            'author_id' => auth()->id(),
            'published' => (bool) $request->boolean('published'),
            'published_at' => $request->boolean('published') ? ($request->date('published_at') ?? now()) : null,
        ]);

        ActivityLogger::log('created news item', 'cms', $item->getKey());

        return redirect()->route('cms.news.edit', $item)
            ->with('status', 'News item created.');
    }

    public function edit(NewsItem $news): View
    {
        $this->authorize('update', $news);

        return view('cms.news.form', ['item' => $news]);
    }

    public function update(UpdateNewsRequest $request, NewsItem $news): RedirectResponse
    {
        $this->authorize('update', $news);

        $news->update([
            'title' => $request->input('title'),
            'slug' => $this->resolveSlug($request, $news),
            'category' => $request->input('category'),
            'excerpt' => $request->input('excerpt'),
            'body' => $request->input('body'),
            'image_path' => $this->storeFeaturedImage($request) ?? $request->input('image_path') ?? $news->image_path,
            'published' => (bool) $request->boolean('published'),
            'published_at' => $request->boolean('published')
                ? ($request->date('published_at') ?? $news->published_at ?? now())
                : null,
        ]);

        ActivityLogger::log('updated news item', 'cms', $news->getKey());

        return redirect()->route('cms.news.edit', $news)
            ->with('status', 'News item updated.');
    }

    public function destroy(NewsItem $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        $news->delete();
        ActivityLogger::log('deleted news item', 'cms', $news->getKey());

        return redirect()->route('cms.news.index')
            ->with('status', 'News item deleted.');
    }

    private function storeFeaturedImage(StoreNewsRequest|UpdateNewsRequest $request): ?string
    {
        if (! $request->hasFile('featured_image')) {
            return null;
        }

        return $request->file('featured_image')->store('cms/news', 'public');
    }

    private function resolveSlug(StoreNewsRequest|UpdateNewsRequest $request, ?NewsItem $item = null): string
    {
        return Str::slug($request->filled('slug')
            ? $request->input('slug')
            : $request->input('title'));
    }
}
