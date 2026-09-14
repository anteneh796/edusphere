<x-layouts.app :title="'Add user'">
    <x-breadcrumb :items="[
        ['label' => 'Users & Roles', 'url' => route('users.index')],
        ['label' => 'Add user'],
    ]" />

    <x-page-header title="Add user" description="Create an account and assign access roles." />

    <div style="max-width: 880px;">
        <x-card>
            <form method="POST" action="{{ route('users.store') }}" novalidate>
                @csrf
                @include('users._form', ['editing' => false])

                <div class="card-footer">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        Create user
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>