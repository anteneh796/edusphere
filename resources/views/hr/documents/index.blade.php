<x-layouts.app :title="__('Documents')">

    <x-page-header :title="__('HR Documents')" :description="__('Certificates, contracts and staff documentation with verification tracking.')">
        @if ($pendingCount > 0)
            <x-badge color="warning" :dot="true">{{ trans_choice(':n document pending review|:n documents pending review', $pendingCount) }}</x-badge>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.documents.index') }}" class="grid gap-1" style="grid-template-columns: 1fr 1fr auto auto; align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="category">{{ __('Category') }}</label>
                <select id="category" name="category" class="form-select">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach (\App\Support\Enums\HrDocumentCategory::cases() as $category)
                        <option value="{{ $category->value }}" @selected(request('category') === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status">{{ __('Verification status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\DocumentVerificationStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            @if (request()->hasAny(['category', 'status']))
                <a href="{{ route('hr.documents.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </form>

        @if (auth()->user()->hasPermission('hr.create'))
            <div style="padding: 0 var(--space-3) var(--space-2); border-top:1px solid var(--color-border);">
                <details class="form-group" style="padding-top: var(--space-2);">
                    <summary class="btn btn-secondary">{{ __('Upload document') }}</summary>
                    <form method="POST" action="{{ route('hr.documents.store') }}" enctype="multipart/form-data" class="grid" style="grid-template-columns: 1fr 1fr 1fr auto; gap: var(--space-2); align-items:end; margin-top: var(--space-2);">
                        @csrf
                        <x-select name="employee_id" :label="__('Employee')" :options="\App\Domains\HumanResources\Models\Employee::whereIn('employment_status', ['active', 'probation'])->orderBy('full_name')->get(['id', 'full_name', 'employee_id'])->mapWithKeys(fn ($e) => [$e->id => $e->full_name.' ('.$e->employee_id.')'])" placeholder="{{ __('Select…') }}" required />
                        <x-select name="category" :label="__('Category')" :options="collect(\App\Support\Enums\HrDocumentCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])" placeholder="{{ __('Select…') }}" required />
                        <x-input name="title" label="{{ __('Title') }}" placeholder="e.g. Degree certificate" required />
                        <input type="file" name="document" class="form-control" required style="grid-column: 1 / -1;" />
                        <div style="grid-column: 1 / -1;" class="flex" style="justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary">{{ __('Upload &amp; queue for review') }}</button>
                        </div>
                    </form>
                </details>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Document') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Uploaded') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $document->title }}</div>
                                <div class="text-xs text-muted">{{ basename($document->file_path) }}</div>
                            </td>
                            <td>
                                <a href="{{ route('hr.employees.show', $document->employee) }}" class="link">{{ $document->employee->full_name }}</a>
                            </td>
                            <td class="text-sm">{{ $document->categoryLabel() }}</td>
                            <td class="text-sm text-muted">{{ $document->uploaded_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <x-badge :color="$document->verificationBadgeColor()" :dot="true">{{ $document->verificationEnum()?->label() ?? $document->verification_status }}</x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.documents.download', $document) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Download') }}">
                                    <x-icon name="download" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.edit'))
                                    <details class="inline" style="display:inline-block; position:relative;">
                                        <summary class="btn btn-ghost btn-sm btn-icon" title="{{ __('Verify') }}">
                                            <x-icon name="shield-check" class="icon-sm" />
                                        </summary>
                                        <form method="POST" action="{{ route('hr.documents.verify', $document) }}" class="card" style="position:absolute; right:0; top:110%; z-index:10; padding: var(--space-2); min-width:260px; box-shadow: var(--shadow);">
                                            @csrf
                                            <x-select name="verification_status" :label="__('Mark as')" :options="collect(\App\Support\Enums\DocumentVerificationStatus::cases())->filter(fn ($s) => $s->value !== $document->verification_status)->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$document->verification_status" required />
                                            <x-textarea name="verified_note" label="{{ __('Note') }}" :rows="2" placeholder="{{ __('Optional note…') }}">{{ old('verified_note') }}</x-textarea>
                                            <button type="submit" class="btn btn-primary btn-sm btn-block">{{ __('Save') }}</button>
                                        </form>
                                    </details>
                                @endif
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.documents.destroy', $document) }}" class="inline" onsubmit="return confirm('{{ __('Delete this document?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--color-danger);" title="{{ __('Delete') }}">
                                            <x-icon name="trash" class="icon-sm" />
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="folder" :title="__('No documents found')" :message="__('Upload staff documents to keep a verifiable record.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $documents->links() }}</div>
    </x-card>

</x-layouts.app>