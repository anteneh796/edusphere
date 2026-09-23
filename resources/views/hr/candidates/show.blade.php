<x-layouts.app :title="$candidate->name">

    <x-page-header :title="$candidate->name" :description="__('Candidate · :position', ['position' => $candidate->applied_position ?? '—'])">
        <div class="flex gap-1">
            <x-badge :color="$candidate->statusBadgeColor()" :dot="true">{{ $candidate->statusLabel() }}</x-badge>
            <a href="{{ route('hr.candidates.index') }}" class="btn btn-secondary">{{ __('Back to candidates') }}</a>
        </div>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 1fr 2fr; gap: var(--space-3); align-items:start;">
        <div class="flex flex-col" style="gap: var(--space-3);">
            <x-card :title="__('Candidate profile')">
                <div class="flex" style="align-items:center; gap: var(--space-2); margin-bottom: var(--space-2);">
                    <x-avatar :name="$candidate->name" size="lg" />
                    <div>
                        <div style="font-weight: var(--weight-semibold);">{{ $candidate->name }}</div>
                        <div class="text-sm text-muted">{{ $candidate->email }}</div>
                    </div>
                </div>
                <div class="flex flex-col" style="gap: var(--space-1); font-size: var(--font-sm);">
                    <div><span class="text-muted">{{ __('Phone') }}:</span> {{ $candidate->phone ?? '—' }}</div>
                    <div><span class="text-muted">{{ __('Applied for') }}:</span> {{ $candidate->applied_position ?? '—' }}</div>
                    <div><span class="text-muted">{{ __('Position (advertised)') }}:</span> {{ $candidate->position?->name ?? '—' }}</div>
                    <div><span class="text-muted">{{ __('Experience') }}:</span> {{ $candidate->experience_years ? $candidate->experience_years.' '.__('yrs') : '—' }}</div>
                    <div><span class="text-muted">{{ __('Interview date') }}:</span> {{ $candidate->interview_date?->format('M j, Y') ?? '—' }}</div>
                    @if ($candidate->cv_path)
                        <div><span class="text-muted">{{ __('CV') }}:</span> <span class="text-sm">{{ basename($candidate->cv_path) }}</span></div>
                    @endif
                    @if ($candidate->convertedEmployee)
                        <div class="text-sm">
                            <span class="text-muted">{{ __('Hired as') }}:</span>
                            <a href="{{ route('hr.employees.show', $candidate->convertedEmployee) }}" class="link">{{ $candidate->convertedEmployee->full_name }} ({{ $candidate->convertedEmployee->employee_id }})</a>
                        </div>
                    @endif
                </div>
            </x-card>

            @if (auth()->user()->hasPermission('hr.edit') && ! in_array($candidate->hiring_status, ['hired', 'rejected']))
                <x-card :title="__('Decision')">
                    <form method="POST" action="{{ route('hr.candidates.decision', $candidate) }}" class="flex flex-col" style="gap: var(--space-2);">
                        @csrf
                        <x-select name="decision" :label="__('Outcome')" :options="['accepted' => __('Shortlist for interview'), 'rejected' => __('Reject application')]" required />
                        <x-textarea name="notes" label="{{ __('Notes') }}" :rows="2" placeholder="{{ __('Reason or next step…') }}">{{ $candidate->decision_notes }}</x-textarea>
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Save decision') }}</button>
                    </form>
                </x-card>
            @endif
        </div>

        <div class="flex flex-col" style="gap: var(--space-3);">
            <x-card :title="__('Pipeline status')">
                @php
                    $pipeline = ['applied', 'interviewing', 'shortlisted', 'offered', 'hired'];
                    $currentIndex = array_search($candidate->hiring_status, $pipeline, true);
                    $currentIndex = $currentIndex === false ? -1 : $currentIndex;
                @endphp
                <div class="flex" style="align-items:center; gap: var(--space-1);">
                    @foreach ($pipeline as $index => $statusValue)
                        @php
                            $active = $index <= $currentIndex;
                            $status = \App\Support\Enums\RecruitmentStatus::tryFrom($statusValue);
                        @endphp
                        <div class="flex-1 flex flex-col" style="gap: var(--space-1); text-align:center;">
                            <div style="width:14px; height:14px; border-radius:50%; margin:0 auto; {{ $active ? 'background: var(--color-primary);' : 'background: var(--color-border);' }}"></div>
                            <div class="text-xs" style="{{ $active ? 'color: var(--color-primary); font-weight: var(--weight-semibold);' : 'color: var(--color-muted);' }}">{{ $status?->label() }}</div>
                        </div>
                        @unless ($loop->last)
                            <div class="flex-1" style="height:2px; {{ $index < $currentIndex ? 'background: var(--color-primary);' : 'background: var(--color-border);' }}"></div>
                        @endunless
                    @endforeach
                </div>
                <div class="text-sm" style="margin-top: var(--space-2);">
                    <span class="text-muted">{{ __('Decision') }}:</span>
                    @if ($candidate->decision)
                        <x-badge :color="$candidate->decision === 'accepted' ? 'success' : 'danger'">{{ $candidate->decision === 'accepted' ? __('Accepted') : __('Rejected') }}</x-badge>
                    @else
                        <span>{{ __('Pending') }}</span>
                    @endif
                </div>
                @if ($candidate->decision_notes)
                    <div class="text-sm" style="margin-top: var(--space-1);"><span class="text-muted">{{ __('Notes') }}:</span> {{ $candidate->decision_notes }}</div>
                @endif
            </x-card>

            @if ($candidate->top_skills)
                <x-card :title="__('Top skills')">
                    <div class="flex" style="gap: var(--space-1); flex-wrap: wrap;">
                        @foreach (collect(explode(',', $candidate->top_skills))->map(fn ($s) => trim($s))->filter() as $skill)
                            <x-badge color="primary">{{ $skill }}</x-badge>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($candidate->notes)
                <x-card :title="__('Notes')">
                    <div class="text-sm">{{ $candidate->notes }}</div>
                </x-card>
            @endif

            @if (auth()->user()->hasPermission('hr.create') && $candidate->hiring_status !== 'hired')
                <x-card :title="__('Hire &amp; create employee record')">
                    <p class="text-sm" style="margin-top:0;">{{ __('Converting this candidate creates a permanent employee record (default: probation).') }}</p>
                    <form method="POST" action="{{ route('hr.candidates.hire', $candidate) }}" class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                        @csrf
                        <x-input name="full_name" label="{{ __('Full name') }}" :value="old('full_name', $candidate->name)" required placeholder="{{ __('Name…') }}" />
                        <x-select name="gender" :label="__('Gender')" :options="['male' => __('Male'), 'female' => __('Female')]" :value="old('gender')" placeholder="{{ __('Select…') }}" />
                        <x-input name="phone" label="{{ __('Phone') }}" :value="old('phone', $candidate->phone)" />
                        <x-select name="department_id" :label="__('Department')" :options="\App\Domains\HumanResources\Models\Department::orderBy('name')->get(['id', 'name'])->mapWithKeys(fn ($d) => [$d->id => $d->name])" placeholder="{{ __('Select…') }}" />
                        <x-select name="position_id" :label="__('Position')" :options="\App\Domains\HumanResources\Models\Position::orderBy('name')->get(['id', 'name'])->mapWithKeys(fn ($p) => [$p->id => $p->name])" :value="old('position_id', $candidate->position_id)" placeholder="{{ __('Select…') }}" />
                        <x-select name="employment_type" :label="__('Employment type')" :options="collect(\App\Support\Enums\EmploymentType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])" :value="old('employment_type', \App\Support\Enums\EmploymentType::Contractual->value)" required />
                        <x-input name="joining_date" type="date" label="{{ __('Joining date') }}" :value="old('joining_date', now()->toDateString())" required />
                        <div class="flex" style="justify-content:flex-end; grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('{{ __('Hire this candidate and create their employee record?') }}')">
                                <x-icon name="user-plus" class="icon-sm" />
                                {{ __('Hire candidate') }}
                            </button>
                        </div>
                    </form>
                </x-card>
            @endif
        </div>
    </div>

</x-layouts.app>