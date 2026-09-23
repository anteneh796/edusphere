<x-layouts.app :title="__('Edit staff member')">
    <x-breadcrumb :items="[
        ['label' => __('Staff'), 'url' => route('staff.index')],
        ['label' => $user->full_name, 'url' => route('staff.show', $user)],
        ['label' => __('Edit')],
    ]" />

    <x-page-header :title="__('Edit')" :description="$user->full_name" />

    <div style="max-width: 880px;">
        <x-card>
            <form method="POST" action="{{ route('staff.update', $user) }}" novalidate>
                @csrf
                @method('PUT')
                @include('staff._form', ['editing' => true])

                <div class="card-footer">
                    <a href="{{ route('staff.show', $user) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>