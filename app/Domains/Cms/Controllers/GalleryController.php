<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Requests\StoreGalleryItemRequest;
use App\Domains\Cms\Requests\UpdateGalleryItemRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', GalleryItem::class);

        return view('cms.gallery.index', [
            'items' => GalleryItem::withTrashed()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', GalleryItem::class);

        return view('cms.gallery.form', ['item' => null]);
    }

    public function store(StoreGalleryItemRequest $request): RedirectResponse
    {
        $this->authorize('create', GalleryItem::class);

        $item = GalleryItem::create([
            'caption' => $request->input('caption'),
            'image_path' => $request->input('image_path'),
            'sort_order' => $request->integer('sort_order'),
            'published' => (bool) $request->boolean('published'),
        ]);

        ActivityLogger::log('created gallery item', 'cms', $item->getKey());

        return redirect()->route('cms.gallery.index')
            ->with('status', 'Gallery item added.');
    }

    public function edit(GalleryItem $gallery_item): View
    {
        $this->authorize('update', $gallery_item);

        return view('cms.gallery.form', ['item' => $gallery_item]);
    }

    public function update(UpdateGalleryItemRequest $request, GalleryItem $gallery_item): RedirectResponse
    {
        $this->authorize('update', $gallery_item);

        $gallery_item->update([
            'caption' => $request->input('caption'),
            'image_path' => $request->input('image_path'),
            'sort_order' => $request->integer('sort_order'),
            'published' => (bool) $request->boolean('published'),
        ]);

        ActivityLogger::log('updated gallery item', 'cms', $gallery_item->getKey());

        return redirect()->route('cms.gallery.index')
            ->with('status', 'Gallery item updated.');
    }

    public function destroy(GalleryItem $gallery_item): RedirectResponse
    {
        $this->authorize('delete', $gallery_item);

        $gallery_item->delete();
        ActivityLogger::log('deleted gallery item', 'cms', $gallery_item->getKey());

        return redirect()->route('cms.gallery.index')
            ->with('status', 'Gallery item deleted.');
    }
}