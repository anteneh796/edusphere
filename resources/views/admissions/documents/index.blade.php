<x-layouts.app :title="__('Applicant documents')">
    <x-breadcrumb :items="[
        ['label' => __('Admissions'), 'url' => route('admissions.dashboard')],
        ['label' => __('Documents')],
    ]" />

    <x-page-header :title="__('Document verification')" :description="__('Verify applicant records uploaded against each application.')" />

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Applicant') }}</th>
                        <th>{{ __('Document') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Verified') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>
                                <a href="{{ route('admissions.applications.show', $document->application) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                    {{ $document->application?->full_name }}
                                </a>
                                <div class="text-xs text-muted">
                                    <span class="code-chip">{{ $document->application?->application_number }}</span>
                                    {{ $document->application?->gradeLevel?->name }}
                                </div>
                            </td>
                            <td class="text-sm">
                                <div>{{ $document->categoryLabel() }}</div>
                                <div class="text-xs text-muted">{{ $document->original_name ?? '—' }}</div>
                            </td>
                            <td>
                                <x-badge :color="$document->statusBadgeColor()">{{ $document->statusLabel() }}</x-badge>
                            </td>
                            <td class="text-sm text-muted">
                                @if ($document->verified_at)
                                    {{ $document->verified_at->format('M j, Y') }}
                                    @if ($document->verifier)
                                        · {{ $document->verifier->full_name }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="actions-cell">
                                @can('verify', $document)
                                    <x-modal title="{{ __('Verify document') }}">
                                        <x-slot:trigger>
                                            <button type="button" class="btn btn-ghost btn-sm btn-icon" :title="__('Verify')">
                                                <x-icon name="check" class="icon-sm" />
                                            </button>
                                        </x-slot:trigger>
                                        <form method="POST" action="{{ route('admissions.documents.verify', $document) }}">
                                            @csrf
                                            <div class="form-group">
                                                <label class="form-label" for="verify-status-{{ $document->id }}">{{ __('Status') }} <span class="required">*</span></label>
                                                <select id="verify-status-{{ $document->id }}" name="status" class="form-select" required>
                                                    @foreach ($statusOptions as $status)
                                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="verify-reason-{{ $document->id }}">{{ __('Rejection reason') }}</label>
                                                <input id="verify-reason-{{ $document->id }}" type="text" name="rejection_reason" class="form-control" placeholder="{{ __('Required when rejected') }}" />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="verify-notes-{{ $document->id }}">{{ __('Notes') }}</label>
                                                <textarea id="verify-notes-{{ $document->id }}" name="notes" class="form-control" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button type="submit" class="btn btn-primary">{{ __('Save verification') }}</button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @endcan
                                @can('delete', $document)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" :title="__('Remove')"
                                        @click="$store.confirm.ask({
                                            title: @js(__('Remove document?')),
                                            message: @js($document->categoryLabel().' '.__('will be deleted.')),
                                            action: @js(route('admissions.documents.destroy', $document)),
                                            method: 'DELETE'
                                        })">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="list" :title="__('No documents uploaded')" :message="__('Documents appear here once uploaded against an application.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $documents->links() }}
        </div>
    </x-card>
</x-layouts.app>