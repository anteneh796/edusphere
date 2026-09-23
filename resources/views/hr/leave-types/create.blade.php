<x-layouts.app :title="__('Add Leave Type')">

    <x-page-header :title="__('Add Leave Type')" :description="__('Define a leave category and its annual entitlement.')">
        <a href="{{ route('hr.leave-types.index') }}" class="btn btn-secondary">{{ __('Back to leave types') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.leave-types.store') }}">
            @csrf
            @include('hr.leave-types._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.leave-types.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Create leave type') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>