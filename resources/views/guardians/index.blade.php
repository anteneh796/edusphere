<x-layouts.app :title="'Guardians'">
    <x-breadcrumb :items="[['label' => 'Guardians']]" />

    <x-page-header title="Guardians" description="Manage parents and guardians linked to student records.">
        @can('create', \App\Domains\Students\Models\Guardian::class)
            <a href="{{ route('guardians.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                Register guardian
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('guardians.index') }}" class="filter-grid">
            <x-input name="q" label="Search" :value="request('q')" placeholder="Name, phone or email…" wrapperClass="filter-q" />

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    Filter
                </button>
                @if (request('q'))
                    <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Guardian</th>
                        <th>Relationship</th>
                        <th>Contact</th>
                        <th>Students</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guardians as $guardian)
                        <tr>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$guardian->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <a href="{{ route('guardians.show', $guardian) }}" style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                            {{ $guardian->full_name }}
                                        </a>
                                        <div class="text-xs text-muted">{{ $guardian->occupation ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <x-badge color="info">{{ $guardian->relationshipLabel() }}</x-badge>
                            </td>
                            <td class="text-sm">
                                <div>{{ $guardian->phone ?? '—' }}</div>
                                <div class="text-xs text-muted">{{ $guardian->email ?? '' }}</div>
                            </td>
                            <td class="text-sm">
                                <span class="code-chip">{{ $guardian->students_count }}</span>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('guardians.show', $guardian) }}" class="btn btn-ghost btn-sm btn-icon" title="View">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @can('update', $guardian)
                                    <a href="{{ route('guardians.edit', $guardian) }}" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endcan
                                @can('delete', $guardian)
                                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger"
                                        title="Delete"
                                        @click="$store.confirm.ask({
                                            title: 'Delete guardian?',
                                            message: `Delete ${@js($guardian->full_name)}. They will be unlinked from any students.`,
                                            action: @js(route('guardians.destroy', $guardian)),
                                            method: 'DELETE',
                                            confirmText: 'Delete'
                                        })">
                                        <x-icon name="trash" class="icon-sm" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="users" title="No guardians found" message="Try adjusting your filters or register a new guardian.">
                                    @can('create', \App\Domains\Students\Models\Guardian::class)
                                        <a href="{{ route('guardians.create') }}" class="btn btn-primary btn-sm">Register guardian</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $guardians->links() }}
        </div>
    </x-card>

</x-layouts.app>