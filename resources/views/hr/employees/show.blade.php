<x-layouts.app :title="__('Employee Profile')">

    <x-page-header :title="$employee->full_name" :description="__('ID: :id · :status', ['id' => $employee->employee_id, 'status' => $employee->statusLabel()])">
        <div class="flex gap-1">
            @if ($canEdit)
                <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-secondary">
                    <x-icon name="pencil" class="icon-sm" />
                    {{ __('Edit') }}
                </a>
            @endif
            @if (auth()->user()->hasPermission('hr.payroll'))
                <a href="{{ route('hr.payroll.edit', $employee) }}" class="btn btn-secondary">
                    <x-icon name="banknote" class="icon-sm" />
                    {{ __('Payroll') }}
                </a>
            @endif
        </div>
    </x-page-header>

    <div class="flex flex-col" style="gap: var(--space-3);">

        <x-card>
            <div class="avatar-cell" style="align-items:center;">
                @if ($employee->photo_path)
                    <img src="{{ asset('storage/'.$employee->photo_path) }}" alt="{{ $employee->full_name }}" style="width:56px; height:56px; border-radius:50%; object-fit:cover;" />
                @else
                    <x-avatar :initials="$employee->initials()" size="lg" />
                @endif
                <div style="min-width:0;">
                    <div style="font-weight: var(--weight-semibold); font-size: var(--font-lg);">{{ $employee->full_name }}</div>
                    <div class="text-sm text-muted">{{ $employee->position?->name }} · {{ $employee->department?->name ?? __('Unassigned') }}</div>
                    <div class="flex gap-1 flex-wrap" style="margin-top: var(--space-1);">
                        <x-badge :color="$employee->statusBadgeColor()" :dot="true">{{ $employee->statusLabel() }}</x-badge>
                        @if ($employee->typeEnum())
                            <x-badge color="neutral">{{ $employee->typeEnum()->label() }}</x-badge>
                        @endif
                        @if ($employee->yearsOfService() > 0)
                            <x-badge color="info">{{ trans_choice(':n year|:n years of service', $employee->yearsOfService()) }}</x-badge>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--space-2); margin-top: var(--space-3);">
                <div class="text-sm"><span class="text-muted">{{ __('Phone') }}:</span> {{ $employee->phone ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Email') }}:</span> {{ $employee->email ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Joined') }}:</span> {{ $employee->joining_date?->format('M j, Y') ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Reports to') }}:</span> {{ $employee->supervisor?->full_name ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Date of birth') }}:</span> {{ $employee->date_of_birth?->format('M j, Y') ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Degree') }}:</span> {{ $employee->degree ?? '—' }}</div>
            </div>
        </x-card>

        @if ($canEdit)
            <x-card :title="__('Change employment status')">
                <form method="POST" action="{{ route('hr.employees.status', $employee) }}" class="grid" style="grid-template-columns: 1fr 2fr auto; gap: var(--space-2); align-items:end;">
                    @csrf
                    @method('PUT')
                    <x-select name="employment_status" :label="__('New status')" :options="collect(\App\Support\Enums\EmploymentStatus::cases())->filter(fn ($s) => $s->value !== $employee->employment_status)->mapWithKeys(fn ($s) => [$s->value => $s->label()])" placeholder="{{ $employee->statusLabel() }}" />
                    <x-input name="notes" label="{{ __('Note (kept in status history)') }}" placeholder="{{ __('Optional reason for this change…') }}" />
                    <button type="submit" class="btn btn-primary">{{ __('Update status') }}</button>
                </form>
            </x-card>
        @endif

        <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
            <x-card :title="__('Qualifications')">
                @if ($employee->qualifications->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr><th>{{ __('Qualification') }}</th><th>{{ __('Institution') }}</th><th>{{ __('Year') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->qualifications as $qualification)
                                    <tr>
                                        <td>{{ $qualification->title }}</td>
                                        <td class="text-sm text-muted">{{ $qualification->institution ?? '—' }}</td>
                                        <td class="text-sm text-muted">{{ $qualification->year ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No qualifications recorded.') }}</div>
                @endif
                @if ($employee->certifications)
                    <div class="text-sm" style="margin-top: var(--space-2);">
                        <span class="text-muted">{{ __('Certifications') }}:</span>
                        <pre style="white-space:pre-wrap; margin:0;">{{ $employee->certifications }}</pre>
                    </div>
                @endif
            </x-card>

            <x-card :title="__('Contracts')">
                @if ($employee->contracts->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr><th>{{ __('Number') }}</th><th>{{ __('Period') }}</th><th>{{ __('Type') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->contracts as $contract)
                                    <tr>
                                        <td>
                                            <a href="{{ route('hr.contracts.show', $contract) }}" class="link">{{ $contract->contract_number }}</a>
                                        </td>
                                        <td class="text-sm text-muted">{{ $contract->start_date->format('M j, Y') }} – {{ $contract->end_date?->format('M j, Y') ?? 'Open' }}</td>
                                        <td class="text-sm">{{ \App\Support\Enums\EmploymentType::from($contract->employment_type)->label() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No contracts yet.') }}</div>
                @endif
                @if (auth()->user()->hasPermission('hr.payroll'))
                    <a href="{{ route('hr.contracts.create') }}" class="btn btn-primary btn-sm" style="margin-top: var(--space-2);">{{ __('New contract') }}</a>
                @endif
            </x-card>
        </div>

        <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
            <x-card :title="__('Recent leave')">
                @if ($employee->leaveRequests->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr><th>{{ __('Type') }}</th><th>{{ __('Days') }}</th><th>{{ __('Status') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->leaveRequests->take(5) as $leave)
                                    <tr>
                                        <td>
                                            <a href="{{ route('hr.leave.show', $leave) }}" class="link">{{ $leave->leaveType?->name }}</a>
                                        </td>
                                        <td class="text-sm text-muted">{{ $leave->days }}</td>
                                        <td>
                                            <x-badge :color="$leave->statusEnum()->badgeColor()">{{ $leave->statusLabel() }}</x-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No leave requests yet.') }}</div>
                @endif
            </x-card>

            <x-card :title="__('Attendance')">
                @if (auth()->user()->hasPermission('hr.create'))
                    <a href="{{ route('hr.leave.create.for', $employee) }}" class="btn btn-primary btn-sm" style="margin-top: var(--space-1);">{{ __('Request leave') }}</a>
                @endif
            </x-card>
        </div>

        <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
            <x-card :title="__('Performance reviews')">
                @if ($employee->performanceReviews->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr><th>{{ __('Period') }}</th><th>{{ __('Overall') }}</th><th>{{ __('Status') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->performanceReviews as $review)
                                    <tr>
                                        <td>
                                            <a href="{{ route('hr.performance.show', $review) }}" class="link">{{ $review->period }}</a>
                                        </td>
                                        <td class="text-sm">{{ $review->overall_score }} / 100</td>
                                        <td><x-badge color="neutral">{{ $review->statusLabel() }}</x-badge></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No reviews yet.') }}</div>
                @endif
            </x-card>

            <x-card :title="__('Training')">
                @if ($employee->trainingRecords->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr><th>{{ __('Course') }}</th><th>{{ __('Date') }}</th><th>{{ __('Hours') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->trainingRecords as $training)
                                    <tr>
                                        <td>{{ $training->course_name }}</td>
                                        <td class="text-sm text-muted">{{ $training->trained_on?->format('M j, Y') ?? '—' }}</td>
                                        <td class="text-sm text-muted">{{ $training->hours ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No training recorded yet.') }}</div>
                @endif
                <a href="{{ route('hr.training.create') }}" class="btn btn-primary btn-sm" style="margin-top: var(--space-2);">{{ __('Add training') }}</a>
            </x-card>
        </div>

        <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-3);">
            <x-card :title="__('Documents')">
                @if ($employee->documents->isNotEmpty())
                    <div class="flex flex-col" style="gap: var(--space-2);">
                        @foreach ($employee->documents as $document)
                            <div class="flex" style="justify-content:space-between; align-items:center; gap: var(--space-2);">
                                <div style="min-width:0;">
                                    <a href="{{ route('hr.documents.download', $document) }}" class="link">{{ $document->title }}</a>
                                    <div class="text-xs text-muted">{{ $document->categoryEnum()?->label() }}</div>
                                </div>
                                <x-badge :color="$document->verificationBadgeColor()">{{ $document->verificationEnum()?->label() ?? $document->verification_status }}</x-badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No documents uploaded yet.') }}</div>
                @endif
                <a href="{{ route('hr.documents.index') }}" class="btn btn-primary btn-sm" style="margin-top: var(--space-2);">{{ __('Upload document') }}</a>
            </x-card>

            <x-card :title="__('Official letters')">
                @if ($employee->officialLetters->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr><th>{{ __('Reference') }}</th><th>{{ __('Type') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($employee->officialLetters as $letter)
                                    <tr>
                                        <td>
                                            <a href="{{ route('hr.letters.show', $letter) }}" class="link">{{ $letter->reference_number }}</a>
                                        </td>
                                        <td class="text-sm text-muted">{{ $letter->typeEnum()?->label() ?? $letter->letter_type }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-muted">{{ __('No official letters yet.') }}</div>
                @endif
            </x-card>
        </div>

        <x-card :title="__('Status history')">
            @if ($employee->statusHistories->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-compact">
                        <thead>
                            <tr><th>{{ __('Changed') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('By') }}</th><th>{{ __('Note') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($employee->statusHistories->sortByDesc('changed_at') as $history)
                                <tr>
                                    <td class="text-sm text-muted">{{ $history->changed_at?->format('M j, Y H:i') }}</td>
                                    <td class="text-sm">{{ $history->old_status ? \App\Support\Enums\EmploymentStatus::tryFrom($history->old_status)?->label() : '—' }}</td>
                                    <td class="text-sm">{{ \App\Support\Enums\EmploymentStatus::tryFrom($history->new_status)?->label() }}</td>
                                    <td class="text-sm">{{ $history->changedBy?->full_name ?? '—' }}</td>
                                    <td class="text-sm text-muted">{{ $history->notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-sm text-muted">{{ __('No status changes recorded.') }}</div>
            @endif
        </x-card>

    </div>

</x-layouts.app>