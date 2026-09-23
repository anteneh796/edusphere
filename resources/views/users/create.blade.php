<x-layouts.app :title="__('Add user')">
    <x-breadcrumb :items="[
        ['label' => __('Users & Roles'), 'url' => route('users.index')],
        ['label' => __('Add user')],
    ]" />

    <x-page-header :title="__('Add user')" :description="__('Create an account and assign access roles.')" />

    <div style="max-width: 880px;">
        <x-card>
            <form method="POST" action="{{ route('users.store') }}" novalidate>
                @csrf
                @include('users._form', ['editing' => false])

                <div class="card-footer">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create user') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>