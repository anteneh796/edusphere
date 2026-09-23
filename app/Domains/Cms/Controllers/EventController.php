<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Requests\StoreEventRequest;
use App\Domains\Cms\Requests\UpdateEventRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::withTrashed()
            ->when($request->query('filter') === 'drafts', fn ($query) => $query->where('published', false))
            ->when($request->query('filter') === 'published', fn ($query) => $query->where('published', true))
            ->orderByDesc('starts_at')
            ->paginate(12)
            ->withQueryString();

        return view('cms.events.index', ['items' => $events]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('cms.events.form', ['item' => null]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            'title' => $request->input('title'),
            'slug' => $this->resolveSlug($request),
            'description' => $request->input('description'),
            'location' => $request->input('location'),
            'starts_at' => $request->date('starts_at'),
            'ends_at' => $request->date('ends_at'),
            'cover_path' => $this->storeCover($request) ?? $request->input('cover_path'),
            'featured' => (bool) $request->boolean('featured'),
            'published' => (bool) $request->boolean('published'),
        ]);

        ActivityLogger::log('created event', 'cms', $event->getKey());

        return redirect()->route('cms.events.edit', $event)
            ->with('status', 'Event created.');
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('cms.events.form', ['item' => $event]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->update([
            'title' => $request->input('title'),
            'slug' => $this->resolveSlug($request, $event),
            'description' => $request->input('description'),
            'location' => $request->input('location'),
            'starts_at' => $request->date('starts_at'),
            'ends_at' => $request->date('ends_at'),
            'cover_path' => $this->storeCover($request) ?? $request->input('cover_path') ?? $event->cover_path,
            'featured' => (bool) $request->boolean('featured'),
            'published' => (bool) $request->boolean('published'),
        ]);

        ActivityLogger::log('updated event', 'cms', $event->getKey());

        return redirect()->route('cms.events.edit', $event)
            ->with('status', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();
        ActivityLogger::log('deleted event', 'cms', $event->getKey());

        return redirect()->route('cms.events.index')
            ->with('status', 'Event deleted.');
    }

    private function storeCover(StoreEventRequest|UpdateEventRequest $request): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }

        return $request->file('cover_image')->store('cms/events', 'public');
    }

    private function resolveSlug(StoreEventRequest|UpdateEventRequest $request, ?Event $event = null): string
    {
        $base = $request->filled('slug')
            ? $request->input('slug')
            : $request->input('title');

        $slug = Str::slug($base);

        if ($event !== null && $event->slug === $slug) {
            return $slug;
        }

        $index = 1;

        while (Event::where('slug', $slug)->where('id', '!=', $event?->id)->exists()) {
            $slug = Str::slug($base).'-'.$index++;
        }

        return $slug;
    }
}
