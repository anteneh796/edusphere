<x-layouts.app :title="__('Edit Contract')">

    <x-page-header :title="__('Edit Contract')" :description="$contract->contract_number">
        <a href="{{ route('hr.contracts.show', $contract) }}" class="btn btn-secondary">{{ __('Back to contract') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.contracts.update', $contract) }}">
            @csrf
            @method('PUT')
            @include('hr.contracts._form')
            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.contracts.show', $contract) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Save changes') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>