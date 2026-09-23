<?php

namespace App\Domains\Cms\Controllers;

use App\Domains\Cms\Models\Testimonial;
use App\Domains\Cms\Requests\StoreTestimonialRequest;
use App\Domains\Cms\Requests\UpdateTestimonialRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Testimonial::class);

        return view('cms.testimonials.index', [
            'items' => Testimonial::withTrashed()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Testimonial::class);

        return view('cms.testimonials.form', ['item' => null]);
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $this->authorize('create', Testimonial::class);

        $item = Testimonial::create([
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'quote' => $request->input('quote'),
            'avatar_path' => $request->hasFile('avatar')
                ? $request->file('avatar')->store('cms/testimonials', 'public')
                : (string) $request->input('avatar_path'),
            'sort_order' => $request->integer('sort_order'),
            'published' => (bool) $request->boolean('published'),
        ]);

        ActivityLogger::log('created testimonial', 'cms', $item->getKey());

        return redirect()->route('cms.testimonials.index')
            ->with('status', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial): View
    {
        $this->authorize('update', $testimonial);

        return view('cms.testimonials.form', ['item' => $testimonial]);
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $testimonial->update([
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'quote' => $request->input('quote'),
            'avatar_path' => $request->hasFile('avatar')
                ? $request->file('avatar')->store('cms/testimonials', 'public')
                : ($request->input('avatar_path') ?? $testimonial->avatar_path),
            'sort_order' => $request->integer('sort_order'),
            'published' => (bool) $request->boolean('published'),
        ]);

        ActivityLogger::log('updated testimonial', 'cms', $testimonial->getKey());

        return redirect()->route('cms.testimonials.index')
            ->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);

        $testimonial->delete();
        ActivityLogger::log('deleted testimonial', 'cms', $testimonial->getKey());

        return redirect()->route('cms.testimonials.index')
            ->with('status', 'Testimonial deleted.');
    }
}
