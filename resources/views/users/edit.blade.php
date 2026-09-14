<x-layouts.app :title="'Edit user'">
    <x-breadcrumb :items="[
        ['label' => 'Users & Roles', 'url' => route('users.index')],
        ['label' => $user->full_name, 'url' => route('users.show', $user)],
        ['label' => 'Edit'],
    ]" />

    <x-page-header title="Edit user" description="Update {{ $user->full_name }}'s account details." />

    <div style="max-width: 880px;">
        <x-card>
            <form method="POST" action="{{ route('users.update', $user) }}" novalidate>
                @csrf
                @method('PUT')
                @include('users._form', ['editing' => true])

                <div class="card-footer">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Save changes
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>