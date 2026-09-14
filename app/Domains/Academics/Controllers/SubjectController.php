<?php

namespace App\Domains\Academics\Controllers;

use App\Domains\Academics\Models\Subject;
use App\Domains\Academics\Requests\StoreSubjectRequest;
use App\Domains\Academics\Requests\UpdateSubjectRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::withCount('assignments as section_count')
            ->ordered()
            ->get();

        return view('academics.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        $this->authorize('create', Subject::class);

        return view('academics.subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Subject::class);

        $subject = Subject::create($request->validated());
        ActivityLogger::log('created subject '.$subject->name.' ('.$subject->code.')', 'academics', $subject->id);

        return redirect()
            ->route('academics.subjects.index')
            ->with('status', "Subject \"{$subject->name}\" created.");
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        return view('academics.subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        $subject->update($request->validated());
        ActivityLogger::log('updated subject '.$subject->name, 'academics', $subject->id);

        return redirect()
            ->route('academics.subjects.index')
            ->with('status', "Subject \"{$subject->name}\" updated.");
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('delete', $subject);

        $subject->delete();
        ActivityLogger::log('deleted subject '.$subject->name, 'academics', $subject->id);

        return redirect()
            ->route('academics.subjects.index')
            ->with('status', "Subject \"{$subject->name}\" deleted.");
    }
}
