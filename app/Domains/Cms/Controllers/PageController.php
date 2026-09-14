<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Requests\UpdatePageRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $this->authorize('view', Page::class);

        return view('cms.pages.index', [
            'pages' => Page::withTrashed()->orderBy('slug')->get(),
        ]);
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);

        return view('cms.pages.edit', [
            'page' => $page,
            'previewUrl' => $page->url(),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        $data = $request->validated();
        $data['published'] = (bool) ($data['published'] ?? false);

        $page->update($data);
        ActivityLogger::log('updated website page', 'cms', $page->getKey());

        return redirect()->route('cms.pages.index')
            ->with('status', "Page “{$page->title}” updated.");
    }
}