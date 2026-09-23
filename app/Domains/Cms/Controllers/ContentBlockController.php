<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\ContentBlock;
use App\Domains\Cms\Requests\StoreContentBlockRequest;
use App\Domains\Cms\Requests\UpdateContentBlockRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentBlockController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ContentBlock::class);

        $groups = ContentBlock::ordered()
            ->get()
            ->groupBy(fn (ContentBlock $block) => $block->page_label);

        return view('cms.content-blocks.index', [
            'groups' => $groups,
            'pageLabels' => ContentBlock::PAGE_LABELS,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ContentBlock::class);

        return view('cms.content-blocks.form', ['contentBlock' => null]);
    }

    public function store(StoreContentBlockRequest $request): RedirectResponse
    {
        $this->authorize('create', ContentBlock::class);

        $contentBlock = ContentBlock::create($this->payload($request));

        ActivityLogger::log('created content block', 'cms', $contentBlock->getKey());

        return redirect()->route('cms.content-blocks.edit', $contentBlock)
            ->with('status', 'Section created.');
    }

    public function edit(ContentBlock $contentBlock): View
    {
        $this->authorize('update', $contentBlock);

        return view('cms.content-blocks.form', ['contentBlock' => $contentBlock]);
    }

    public function update(UpdateContentBlockRequest $request, ContentBlock $contentBlock): RedirectResponse
    {
        $this->authorize('update', $contentBlock);

        $contentBlock->update($this->payload($request));

        ActivityLogger::log('updated content block', 'cms', $contentBlock->getKey());

        return redirect()->route('cms.content-blocks.edit', $contentBlock)
            ->with('status', 'Section updated.');
    }

    public function destroy(ContentBlock $contentBlock): RedirectResponse
    {
        $this->authorize('delete', $contentBlock);

        $contentBlock->delete();
        ActivityLogger::log('deleted content block', 'cms', $contentBlock->getKey());

        return redirect()->route('cms.content-blocks.index')
            ->with('status', 'Section deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('update', ContentBlock::class);

        $request->validate([
            'orders' => ['required', 'array'],
            'orders.*' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        foreach ($request->input('orders', []) as $id => $sort) {
            if ($block = ContentBlock::find($id)) {
                $block->update(['sort_order' => (int) $sort]);
            }
        }

        ContentBlock::forgetCache();

        return redirect()->route('cms.content-blocks.index')
            ->with('status', 'Section order saved.');
    }

    private function payload(StoreContentBlockRequest|UpdateContentBlockRequest $request): array
    {
        $raw = json_decode((string) $request->input('payload', ''), true);

        $items = [];

        foreach (($raw['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $entry = array_filter([
                'icon' => $item['icon'] ?? null,
                'title' => $item['title'] ?? null,
                'text' => $item['text'] ?? null,
                'detail' => $item['detail'] ?? null,
            ], static fn ($value) => $value !== null && trim((string) $value) !== '');

            $pointsText = $item['points_text'] ?? '';

            if (trim((string) $pointsText) !== '') {
                $points = array_values(array_filter(
                    array_map('trim', preg_split('/\r?\n/', (string) $pointsText)),
                    static fn ($point) => $point !== '',
                ));

                if ($points !== []) {
                    $entry['points'] = $points;
                }
            }

            if ($entry !== []) {
                $items[] = $entry;
            }
        }

        return [
            'page_slug' => $request->input('page_slug'),
            'key' => $request->input('key'),
            'eyebrow' => $request->input('eyebrow'),
            'title' => $request->input('title'),
            'lead' => $request->input('lead'),
            'body' => $request->input('body'),
            'payload' => $items !== [] ? ['items' => $items] : null,
            'sort_order' => (int) $request->input('sort_order', 0),
            'published' => $request->boolean('published'),
        ];
    }
}
