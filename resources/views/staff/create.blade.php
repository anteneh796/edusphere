<x-layouts.app :title="__('Add staff member')">
    <x-breadcrumb :items="[
        ['label' => __('Staff'), 'url' => route('staff.index')],
        ['label' => __('Add staff member')],
    ]" />

    <x-page-header :title="__('Add staff member')" :description="__('Create an account, choose roles and record staff details.')" />

    <div style="max-width: 880px;">
        <x-card>
            <form method="POST" action="{{ route('staff.store') }}" novalidate>
                @csrf
                @include('staff._form', ['editing' => false])

                <div class="card-footer">
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Create staff member') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>