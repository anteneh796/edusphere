<x-layouts.app :title="__('New Contract')">

    <x-page-header :title="__('New Contract')" :description="__('Create an employment contract for a staff member.')">
        <a href="{{ route('hr.contracts.index') }}" class="btn btn-secondary">{{ __('Back to contracts') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.contracts.store') }}">
            @csrf
            @include('hr.contracts._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.contracts.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Create contract') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>